<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAdvanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Employee $employee;
    protected Product $product;

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

        $this->employee = Employee::create([
            'name'       => 'Tariq Mehmood',
            'phone'      => '03009876543',
            'address'    => 'Phase 8, Bahria Town',
            'is_active'  => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'hardware'],
            ['name' => 'Hardware', 'is_active' => true]
        );

        $this->product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Submersible Pump',
            'slug'                => 'submersible-pump',
            'sku'                 => 'PMP-001',
            'unit'                => 'pc',
            'cost_price'          => 5000.00,
            'sale_price'          => 8000.00,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 2,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        // Employee takes 8,000 product on credit
        Sale::create([
            'invoice_number'  => 'INV-20260311-0099',
            'user_id'         => $this->cashier->id,
            'employee_id'     => $this->employee->id,
            'customer_name'   => $this->employee->name,
            'customer_phone'  => $this->employee->phone,
            'subtotal'        => 8000.00,
            'discount_amount' => 0.00,
            'tax_amount'      => 0.00,
            'total_amount'    => 8000.00,
            'paid_amount'     => 0.00,
            'change_amount'   => 0.00,
            'payment_method'  => 'credit',
            'status'          => 'completed',
        ]);
    }

    public function test_cashier_can_record_employee_payment_and_clear_dues(): void
    {
        // Initial pending payment = 8000
        // Employee pays back 5000 in cash at the POS counter
        // Remaining balance should be 3000
        $this->assertEquals(8000.00, (float)$this->employee->pending_payment);

        $payload = [
            'employee_id'    => $this->employee->id,
            'amount'         => 5000.00,
            'payment_method' => 'cash',
            'notes'          => 'Employee returned advance payment at counter',
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.employee-payments'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('raw_new_balance', 3000);

        $this->employee->refresh();
        $this->assertEquals(3000.00, (float)$this->employee->pending_payment);

        $payment = EmployeePayment::latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(5000.00, (float)$payment->amount);
        $this->assertEquals($this->cashier->id, $payment->user_id);
    }

    public function test_employee_receipt_voucher_renders_correctly(): void
    {
        $payment = EmployeePayment::create([
            'employee_id'    => $this->employee->id,
            'amount'         => 5000.00,
            'payment_method' => 'cash',
            'payment_date'   => now()->toDateString(),
            'user_id'        => $this->cashier->id,
            'notes'          => 'Payment Clearance Voucher',
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.pos.employee-receipt', $payment->id));

        $response->assertOk()
            ->assertSee('Tariq Mehmood')
            ->assertSee('5,000')
            ->assertSee('Hassan & Sons');
    }

    public function test_admin_can_view_employee_ledger(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.employees.ledger', $this->employee->id));

        $response->assertOk()
            ->assertSee('Tariq Mehmood')
            ->assertSee('8,000');
    }
}
