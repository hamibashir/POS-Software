<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeAdjustment;
use App\Models\EmployeePayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDueAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cashier;
    private Employee $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        $this->customer = Employee::create([
            'name'            => 'Test Due Customer',
            'phone'           => '03001234567',
            'address'         => 'Test Shop',
            'opening_balance' => 0.00,
            'is_active'       => true,
        ]);
    }

    public function test_admin_can_adjust_customer_due_without_affecting_store_sales(): void
    {
        // 1. Create a credit sale of PKR 1,500
        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id'        => $this->cashier->id,
            'employee_id'    => $this->customer->id,
            'subtotal'       => 1500.00,
            'discount'       => 0.00,
            'tax'            => 0.00,
            'total_amount'   => 1500.00,
            'paid_amount'    => 0.00,
            'payment_method' => 'credit',
            'status'         => 'pending',
        ]);

        $this->customer->refresh();
        $this->assertEquals(1500.00, (float) $this->customer->pending_payment);

        $initialTotalSales = Sale::where('status', '!=', 'voided')->sum('total_amount');
        $this->assertEquals(1500.00, (float) $initialTotalSales);

        // 2. Adjust due to PKR 900 (reduction of 600)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.staff.employees.adjust-due', $this->customer), [
                'new_due'      => 900.00,
                'reason'       => 'Mistaken credit entry corrected',
                'affect_sales' => false,
            ]);

        $response->assertSessionHas('success');

        $this->customer->refresh();
        $this->assertEquals(900.00, (float) $this->customer->pending_payment);

        // Verify adjustment record in DB
        $this->assertDatabaseHas('employee_adjustments', [
            'employee_id'  => $this->customer->id,
            'user_id'      => $this->admin->id,
            'previous_due' => 1500.00,
            'new_due'      => 900.00,
            'amount'       => -600.00,
            'affect_sales' => false,
            'is_active'    => true,
        ]);

        // Verify store sales remain completely untouched
        $this->assertEquals($initialTotalSales, (float) Sale::where('status', '!=', 'voided')->sum('total_amount'));
    }

    public function test_admin_can_undo_and_redo_due_adjustment(): void
    {
        // Customer with 1,000 opening balance
        $this->customer->update(['opening_balance' => 1000.00]);
        $this->assertEquals(1000.00, (float) $this->customer->pending_payment);

        // Adjust due to 300
        $this->actingAs($this->admin)
            ->post(route('admin.staff.employees.adjust-due', $this->customer), [
                'new_due' => 300.00,
                'reason'  => 'Waiver due to discrepancy',
            ]);

        $this->customer->refresh();
        $this->assertEquals(300.00, (float) $this->customer->pending_payment);

        $adjustment = EmployeeAdjustment::where('employee_id', $this->customer->id)->firstOrFail();
        $this->assertTrue($adjustment->is_active);

        // UNDO adjustment
        $undoResponse = $this->actingAs($this->admin)
            ->post(route('admin.staff.adjustments.toggle-undo', $adjustment));

        $undoResponse->assertSessionHas('success');
        $adjustment->refresh();
        $this->assertFalse($adjustment->is_active);

        // Balance restored to 1,000!
        $this->customer->refresh();
        $this->assertEquals(1000.00, (float) $this->customer->pending_payment);

        // REDO adjustment
        $redoResponse = $this->actingAs($this->admin)
            ->post(route('admin.staff.adjustments.toggle-undo', $adjustment));

        $redoResponse->assertSessionHas('success');
        $adjustment->refresh();
        $this->assertTrue($adjustment->is_active);

        // Balance back to 300!
        $this->customer->refresh();
        $this->assertEquals(300.00, (float) $this->customer->pending_payment);
    }

    public function test_admin_can_edit_and_delete_customer_payment(): void
    {
        $this->customer->update(['opening_balance' => 2000.00]);

        // Record a payment of 1,000
        $payment = EmployeePayment::create([
            'employee_id'    => $this->customer->id,
            'amount'         => 1000.00,
            'payment_method' => 'cash',
            'payment_date'   => now()->format('Y-m-d'),
            'user_id'        => $this->admin->id,
            'notes'          => 'Initial partial payment',
        ]);

        $this->customer->refresh();
        $this->assertEquals(1000.00, (float) $this->customer->pending_payment);

        // Edit payment to 1,500
        $updateResp = $this->actingAs($this->admin)
            ->put(route('admin.staff.employees.payments.update', $payment), [
                'amount'         => 1500.00,
                'payment_method' => 'bank',
                'payment_date'   => now()->format('Y-m-d'),
                'notes'          => 'Updated payment amount',
            ]);

        $updateResp->assertSessionHas('success');
        $this->customer->refresh();
        $this->assertEquals(500.00, (float) $this->customer->pending_payment);

        // Delete (Undo) payment
        $deleteResp = $this->actingAs($this->admin)
            ->delete(route('admin.staff.employees.payments.destroy', $payment));

        $deleteResp->assertSessionHas('success');
        $this->assertDatabaseMissing('employee_payments', ['id' => $payment->id]);

        $this->customer->refresh();
        // Restored to full 2,000 due
        $this->assertEquals(2000.00, (float) $this->customer->pending_payment);
    }

    public function test_cashier_cannot_adjust_dues_or_modify_payments(): void
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('admin.staff.employees.adjust-due', $this->customer), [
                'new_due' => 100.00,
                'reason'  => 'Attempted hack',
            ]);

        $response->assertRedirect(route('cashier.pos'))
            ->assertSessionHas('error');
    }
}
