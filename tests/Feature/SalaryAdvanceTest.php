<?php

namespace Tests\Feature;

use App\Models\SalaryAdvance;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryAdvanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'Admin User',
            'email'     => 'admin@example.com',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name'           => 'Bilal Cashier',
            'email'          => 'bilal@example.com',
            'role'           => 'cashier',
            'salary'         => 35000.00,
            'allowed_leaves' => 2,
            'is_active'      => true,
        ]);
    }

    public function test_admin_can_record_salary_advance_for_employee(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.advances.store'), [
            'user_id'        => $this->cashier->id,
            'amount'         => 5000.00,
            'date'           => '2026-03-12',
            'payment_method' => 'cash',
            'notes'          => 'Mid-month personal emergency advance',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $advance = SalaryAdvance::latest('id')->first();
        $this->assertNotNull($advance);
        $this->assertEquals($this->cashier->id, $advance->user_id);
        $this->assertEquals(5000.00, (float)$advance->amount);
        $this->assertEquals('2026-03-12', $advance->date->format('Y-m-d'));
        $this->assertEquals('cash', $advance->payment_method);
        $this->assertEquals('pending', $advance->status);
        $this->assertEquals($this->admin->id, $advance->created_by);
    }

    public function test_advance_appears_in_attendance_index_and_stats(): void
    {
        SalaryAdvance::create([
            'user_id'        => $this->cashier->id,
            'amount'         => 3500.00,
            'date'           => now()->toDateString(),
            'payment_method' => 'cash',
            'notes'          => 'Advance for fuel',
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('Salary Advances');
        $response->assertSee('Bilal Cashier');
        $response->assertSee('3,500');
        $response->assertSee('Advance for fuel');
    }

    public function test_monthly_payroll_automatically_deducts_salary_advance(): void
    {
        // Monthly base salary = 35,000
        // Record 2 advances in March 2026: 4,000 + 3,000 = 7,000 total
        SalaryAdvance::create([
            'user_id'        => $this->cashier->id,
            'amount'         => 4000.00,
            'date'           => '2026-03-05',
            'payment_method' => 'cash',
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);

        SalaryAdvance::create([
            'user_id'        => $this->cashier->id,
            'amount'         => 3000.00,
            'date'           => '2026-03-18',
            'payment_method' => 'bank',
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);

        $payroll = $this->cashier->calculateMonthlyPayroll(2026, 3);

        $this->assertEquals(35000.00, $payroll['base_salary']);
        $this->assertEquals(7000.00, $payroll['advance_amount']);
        $this->assertEquals(2, count($payroll['advances']));
        // With 0 excess absences, net payable = 35,000 - 7,000 = 28,000
        $this->assertEquals(28000.00, $payroll['net_payable']);
    }

    public function test_admin_can_update_advance(): void
    {
        $advance = SalaryAdvance::create([
            'user_id'        => $this->cashier->id,
            'amount'         => 2000.00,
            'date'           => '2026-03-10',
            'payment_method' => 'cash',
            'notes'          => 'Initial advance',
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.attendance.advances.update', $advance->id), [
            'amount'         => 2500.00,
            'date'           => '2026-03-11',
            'payment_method' => 'bank',
            'notes'          => 'Corrected advance amount and method',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $advance->refresh();
        $this->assertEquals(2500.00, (float)$advance->amount);
        $this->assertEquals('2026-03-11', $advance->date->format('Y-m-d'));
        $this->assertEquals('bank', $advance->payment_method);
        $this->assertEquals('Corrected advance amount and method', $advance->notes);
    }

    public function test_admin_can_delete_pending_advance(): void
    {
        $advance = SalaryAdvance::create([
            'user_id'        => $this->cashier->id,
            'amount'         => 1500.00,
            'date'           => '2026-03-15',
            'payment_method' => 'cash',
            'notes'          => 'Test delete advance',
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.attendance.advances.destroy', $advance->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('salary_advances', ['id' => $advance->id]);
    }

    public function test_salary_disbursement_deducts_advance_and_marks_status_deducted(): void
    {
        // Give 5,000 advance in March 2026
        $advance = SalaryAdvance::create([
            'user_id'        => $this->cashier->id,
            'amount'         => 5000.00,
            'date'           => '2026-03-10',
            'payment_method' => 'cash',
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);

        // Disburse salary for 2026-03
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.payroll.pay', $this->cashier->id), [
            'month_year'        => '2026-03',
            'base_salary'       => 35000.00,
            'deduction_amount'  => 0.00,
            'advance_deduction' => 5000.00,
            'bonus_amount'      => 0.00,
            'payment_method'    => 'bank',
            'payment_date'      => '2026-03-31',
            'notes'             => 'March salary disbursed after advance deduction',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check SalaryPayment
        $payment = SalaryPayment::where('user_id', $this->cashier->id)
            ->where('month_year', '2026-03')
            ->first();

        $this->assertNotNull($payment);
        $this->assertEquals(35000.00, (float)$payment->base_salary);
        $this->assertEquals(5000.00, (float)$payment->advance_deduction);
        $this->assertEquals(30000.00, (float)$payment->net_payable);

        // Check SalaryAdvance status updated to 'deducted' and linked to payment
        $advance->refresh();
        $this->assertEquals('deducted', $advance->status);
        $this->assertEquals($payment->id, $advance->salary_payment_id);

        // Verify Salary Slip displays advance deduction
        $slipResponse = $this->actingAs($this->admin)->get(route('admin.attendance.payroll.slip', $payment->id));
        $slipResponse->assertStatus(200);
        $slipResponse->assertSee('Advance Salary Deductions');
        $slipResponse->assertSee('5,000');
        $slipResponse->assertSee('30,000');
    }

    public function test_cashier_cannot_record_or_modify_advance(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('admin.attendance.advances.store'), [
            'user_id'        => $this->cashier->id,
            'amount'         => 1000.00,
            'date'           => '2026-03-10',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('cashier.pos'));
        $this->assertDatabaseMissing('salary_advances', ['amount' => 1000.00]);
    }
}
