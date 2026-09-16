<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name'      => 'Tools',
            'slug'      => 'tools',
            'is_active' => true,
        ]);

        $this->productA = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Hammer Pro',
            'slug'                => 'hammer-pro',
            'sku'                 => 'HAM-001',
            'barcode'             => '1234567890',
            'unit'                => 'pc',
            'cost_price'          => 300.00,
            'sale_price'          => 500.00,
            'stock_quantity'      => 50,
            'low_stock_threshold' => 10,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        $this->productB = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Wrench 12mm',
            'slug'                => 'wrench-12mm',
            'sku'                 => 'WRN-012',
            'barcode'             => '9876543210',
            'unit'                => 'pc',
            'cost_price'          => 150.00,
            'sale_price'          => 250.00,
            'stock_quantity'      => 30,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);
    }

    public function test_cashier_can_complete_sale_with_exact_cash(): void
    {
        $payload = [
            'cart' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity'   => 2,
                    'unit_price' => 500.00,
                ],
                [
                    'product_id' => $this->productB->id,
                    'quantity'   => 1,
                    'unit_price' => 250.00,
                ],
            ],
            'discount_amount' => 50.00,
            'payment_method'  => 'cash',
            'paid_amount'     => 1200.00,
            'customer_name'   => 'John Doe',
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Expected financial values:
        // Subtotal = (2 * 500) + (1 * 250) = 1250
        // Discount = 50
        // Total = 1200
        // Paid = 1200
        // Change = 0
        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(1250.00, (float)$sale->subtotal);
        $this->assertEquals(50.00, (float)$sale->discount_amount);
        $this->assertEquals(1200.00, (float)$sale->total_amount);
        $this->assertEquals(1200.00, (float)$sale->paid_amount);
        $this->assertEquals(0.00, (float)$sale->change_amount);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals('John Doe', $sale->customer_name);

        // Verify stock deducted
        $this->productA->refresh();
        $this->productB->refresh();
        $this->assertEquals(48, $this->productA->stock_quantity);
        $this->assertEquals(29, $this->productB->stock_quantity);

        // Verify StockMovement recorded
        $movements = StockMovement::where('sale_id', $sale->id)
            ->where('type', 'sale')
            ->get();
        $this->assertCount(2, $movements);
    }

    public function test_sale_with_cash_change_calculation(): void
    {
        $payload = [
            'cart' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity'   => 1,
                    'unit_price' => 500.00,
                ],
            ],
            'discount_amount' => 0.00,
            'payment_method'  => 'cash',
            'paid_amount'     => 1000.00, // Customer pays 1000 for 500 item
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $sale = Sale::latest('id')->first();
        $this->assertEquals(500.00, (float)$sale->total_amount);
        $this->assertEquals(1000.00, (float)$sale->paid_amount);
        $this->assertEquals(500.00, (float)$sale->change_amount);
    }

    public function test_sale_with_card_payment(): void
    {
        $payload = [
            'cart' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity'   => 2,
                    'unit_price' => 500.00,
                ],
            ],
            'discount_amount' => 100.00,
            'payment_method'  => 'card',
            'paid_amount'     => 900.00,
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $sale = Sale::latest('id')->first();
        $this->assertEquals(1000.00, (float)$sale->subtotal);
        $this->assertEquals(100.00, (float)$sale->discount_amount);
        $this->assertEquals(900.00, (float)$sale->total_amount);
        $this->assertEquals('card', $sale->payment_method);
    }

    public function test_sale_fails_when_insufficient_stock(): void
    {
        $payload = [
            'cart' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity'   => 999, // Stock is only 50
                    'unit_price' => 500.00,
                ],
            ],
            'payment_method'  => 'cash',
            'paid_amount'     => 500.00,
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_credit_sale_assigns_employee_and_records_transaction(): void
    {
        $employee = Employee::create([
            'name'       => 'Ali Hassan',
            'role'       => 'Technician',
            'phone'      => '03001234567',
            'address'    => 'Rawalpindi',
            'salary'     => 35000,
            'is_active'  => true,
        ]);

        $payload = [
            'cart' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity'   => 1,
                    'unit_price' => 500.00,
                ],
            ],
            'payment_method'  => 'credit',
            'employee_id'     => $employee->id,
            'paid_amount'     => 0,
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $sale = Sale::latest('id')->first();
        $this->assertEquals($employee->id, $sale->employee_id);
        $this->assertEquals('credit', $sale->payment_method);
        $this->assertEquals(0, (float)$sale->paid_amount);
        $this->assertEquals(500.00, (float)$sale->total_amount);
    }

    public function test_sale_receipt_renders_correctly(): void
    {
        $payload = [
            'cart' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity'   => 1,
                    'unit_price' => 500.00,
                ],
            ],
            'payment_method'  => 'cash',
            'paid_amount'     => 500.00,
        ];

        $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $sale = Sale::latest('id')->first();

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.pos.receipt', $sale->id));

        $response->assertOk()
            ->assertSee($sale->invoice_number)
            ->assertSee('Hammer Pro')
            ->assertSee('Hassan & Sons');
    }

    public function test_sales_listing_can_filter_by_credit_payment_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $employee = Employee::create([
            'name'       => 'Kamran Khan',
            'phone'      => '03111223344',
            'address'    => 'Islamabad',
            'is_active'  => true,
        ]);

        // Create 1 cash sale and 1 credit sale
        $cashSale = Sale::create([
            'invoice_number'  => 'INV-CASH-001',
            'user_id'         => $admin->id,
            'subtotal'        => 500,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'total_amount'    => 500,
            'paid_amount'     => 500,
            'change_amount'   => 0,
            'payment_method'  => 'cash',
            'status'          => 'completed',
        ]);

        $creditSale = Sale::create([
            'invoice_number'  => 'INV-CREDIT-001',
            'user_id'         => $admin->id,
            'employee_id'     => $employee->id,
            'customer_name'   => $employee->name,
            'subtotal'        => 1500,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'total_amount'    => 1500,
            'paid_amount'     => 0,
            'change_amount'   => 0,
            'payment_method'  => 'credit',
            'status'          => 'completed',
        ]);

        // Filter by credit
        $response = $this->actingAs($admin)
            ->get(route('admin.sales.index', ['payment_method' => 'credit']));

        $response->assertOk()
            ->assertSee('INV-CREDIT-001')
            ->assertSee('CREDIT')
            ->assertDontSee('INV-CASH-001');

        // Filter by cash
        $responseCash = $this->actingAs($admin)
            ->get(route('admin.sales.index', ['payment_method' => 'cash']));

        $responseCash->assertOk()
            ->assertSee('INV-CASH-001')
            ->assertDontSee('INV-CREDIT-001');
    }

    public function test_unpaid_credit_sale_displays_pending_status_in_sales_window(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $employee = Employee::create([
            'name'       => 'Zubair Shah',
            'phone'      => '03221122334',
            'address'    => 'Rawalpindi',
            'is_active'  => true,
        ]);

        // Unpaid credit sale
        $creditSale = Sale::create([
            'invoice_number'  => 'INV-CREDIT-PENDING-001',
            'user_id'         => $admin->id,
            'employee_id'     => $employee->id,
            'customer_name'   => $employee->name,
            'subtotal'        => 2500,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'total_amount'    => 2500,
            'paid_amount'     => 0,
            'change_amount'   => 0,
            'payment_method'  => 'credit',
            'status'          => 'pending',
        ]);

        // Sales listing should display "Pending"
        $response = $this->actingAs($admin)
            ->get(route('admin.sales.index'));

        $response->assertOk()
            ->assertSee('INV-CREDIT-PENDING-001')
            ->assertSee('Pending');

        // Filter by pending status
        $pendingFilterResponse = $this->actingAs($admin)
            ->get(route('admin.sales.index', ['status' => 'pending']));

        $pendingFilterResponse->assertOk()
            ->assertSee('INV-CREDIT-PENDING-001');

        // Show view should also display "Pending"
        $showResponse = $this->actingAs($admin)
            ->get(route('admin.sales.show', $creditSale->id));

        $showResponse->assertOk()
            ->assertSee('Pending');

        // Now clear the credit payment
        $payRes = $this->actingAs($admin)
            ->post(route('admin.staff.employees.payments', $employee->id), [
                'amount'         => 2500,
                'payment_method' => 'cash',
                'payment_date'   => now()->toDateString(),
            ]);

        $payRes->assertSessionHasNoErrors();
        $creditSale->refresh();
        $this->assertEquals('completed', $creditSale->status);

        // Sales listing should now display "Completed"
        $afterPaymentResponse = $this->actingAs($admin)
            ->get(route('admin.sales.show', $creditSale->id));

        $afterPaymentResponse->assertOk()
            ->assertSee('Completed');
    }
}
