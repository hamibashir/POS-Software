<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerReturnAndFinancialAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Category $category;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $this->category = Category::firstOrCreate(
            ['slug' => 'hardware-audit'],
            ['name' => 'Hardware Audit', 'is_active' => true]
        );

        $this->productA = Product::create([
            'name'           => 'Hammer Pro',
            'slug'           => 'hammer-pro-audit',
            'sku'            => 'HAM-01-AUDIT',
            'category_id'    => $this->category->id,
            'cost_price'     => 400.00,
            'sale_price'     => 700.00,
            'stock_quantity' => 20,
            'unit'           => 'pcs',
            'is_active'      => true,
        ]);

        $this->productB = Product::create([
            'name'           => 'Drill Bit 10mm',
            'slug'           => 'drill-bit-10mm-audit',
            'sku'            => 'DB-10-AUDIT',
            'category_id'    => $this->category->id,
            'cost_price'     => 150.00,
            'sale_price'     => 300.00,
            'stock_quantity' => 50,
            'unit'           => 'pcs',
            'is_active'      => true,
        ]);
    }

    public function test_cashier_can_process_return_with_invoice_lookup_and_restore_stock(): void
    {
        // 1. Create a completed sale: 5x Hammer @ 700 = 3500
        $sale = Sale::create([
            'invoice_number' => 'INV-20260323-0001',
            'user_id'        => $this->cashier->id,
            'customer_name'  => 'Tariq Mehmood',
            'customer_phone' => '03001234567',
            'subtotal'       => 3500.00,
            'discount_amount'=> 0.00,
            'tax_amount'     => 0.00,
            'total_amount'   => 3500.00,
            'paid_amount'    => 3500.00,
            'change_amount'  => 0.00,
            'payment_method' => 'cash',
            'status'         => 'completed',
        ]);

        $saleItem = SaleItem::create([
            'sale_id'         => $sale->id,
            'product_id'      => $this->productA->id,
            'product_name'    => $this->productA->name,
            'product_sku'     => $this->productA->sku,
            'product_unit'    => $this->productA->unit,
            'quantity'        => 5,
            'cost_price'      => $this->productA->cost_price,
            'unit_price'      => $this->productA->sale_price,
            'discount_amount' => 0.00,
            'total_price'     => 3500.00,
        ]);

        $this->productA->decrement('stock_quantity', 5);
        $this->assertEquals(15, $this->productA->fresh()->stock_quantity);

        // 2. Lookup Sale via AJAX
        $response = $this->actingAs($this->cashier)
            ->getJson(route('cashier.pos.lookup-sale', ['invoice' => $sale->invoice_number]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sale.invoice_number', $sale->invoice_number)
            ->assertJsonPath('sale.items.0.quantity_returnable', 5);

        // 3. Process return of 2 units
        $returnPayload = [
            'sale_id'        => $sale->id,
            'customer_name'  => 'Tariq Mehmood',
            'customer_phone' => '03001234567',
            'refund_method'  => 'cash',
            'reason'         => 'Defective / Damaged Item',
            'notes'          => 'Exchanged item returned',
            'items'          => [
                [
                    'product_id'   => $this->productA->id,
                    'sale_item_id' => $saleItem->id,
                    'quantity'     => 2,
                    'unit_price'   => 700.00,
                ]
            ]
        ];

        $processResponse = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.process-return'), $returnPayload);

        $processResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('refund_amount', '1,400.00');

        // 4. Assert stock was restored: 15 + 2 = 17
        $this->assertEquals(17, $this->productA->fresh()->stock_quantity);

        // 5. Assert stock movement was logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id'   => $this->productA->id,
            'type'         => 'return',
            'quantity'     => 2,
            'stock_before' => 15,
            'stock_after'  => 17,
        ]);

        // 6. Assert sale return records created
        $this->assertDatabaseHas('sale_returns', [
            'sale_id'             => $sale->id,
            'customer_name'       => 'Tariq Mehmood',
            'total_return_amount' => 1400.00,
            'refund_method'       => 'cash',
        ]);
    }

    public function test_credit_return_adjusts_customer_credit_balance(): void
    {
        $customer = Employee::create([
            'name'      => 'Asim Shah',
            'phone'     => '03335558888',
            'address'   => 'Bahria Phase 8',
            'is_active' => true,
        ]);

        // Credit Sale of 3x Drill bits @ 300 = 900
        $sale = Sale::create([
            'invoice_number' => 'INV-20260323-0002',
            'user_id'        => $this->cashier->id,
            'employee_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'customer_phone' => $customer->phone,
            'subtotal'       => 900.00,
            'discount_amount'=> 0.00,
            'tax_amount'     => 0.00,
            'total_amount'   => 900.00,
            'paid_amount'    => 0.00,
            'change_amount'  => 0.00,
            'payment_method' => 'credit',
            'status'         => 'pending',
        ]);

        SaleItem::create([
            'sale_id'         => $sale->id,
            'product_id'      => $this->productB->id,
            'product_name'    => $this->productB->name,
            'product_sku'     => $this->productB->sku,
            'product_unit'    => $this->productB->unit,
            'quantity'        => 3,
            'cost_price'      => $this->productB->cost_price,
            'unit_price'      => $this->productB->sale_price,
            'discount_amount' => 0.00,
            'total_price'     => 900.00,
        ]);

        $this->assertEquals(900.00, $customer->fresh()->pending_payment);

        // Return 1 Drill bit on credit adjustment (refund 300)
        $returnPayload = [
            'sale_id'        => $sale->id,
            'employee_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'customer_phone' => $customer->phone,
            'refund_method'  => 'credit_adjustment',
            'reason'         => 'Excess / Leftover Quantity',
            'items'          => [
                [
                    'product_id' => $this->productB->id,
                    'quantity'   => 1,
                    'unit_price' => 300.00,
                ]
            ]
        ];

        $processResponse = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.process-return'), $returnPayload);

        $processResponse->assertOk()
            ->assertJsonPath('success', true);

        // Customer due should now be: 900 - 300 = 600
        $this->assertEquals(600.00, $customer->fresh()->pending_payment);
    }

    public function test_admin_can_view_sale_returns_index_and_show_views(): void
    {
        $saleReturn = SaleReturn::create([
            'return_number'       => 'RET-20260323-0001',
            'customer_name'       => 'Bilal Ahmed',
            'customer_phone'      => '03211112222',
            'user_id'             => $this->cashier->id,
            'total_return_amount' => 600.00,
            'refund_amount'       => 600.00,
            'refund_method'       => 'cash',
            'refund_status'       => 'completed',
            'returned_at'         => now()->toDateString(),
            'reason'              => 'Customer Changed Mind',
        ]);

        // Admin Index
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.sale-returns.index'));
        $indexResponse->assertOk()
            ->assertSee('Customer Sale Returns')
            ->assertSee('RET-20260323-0001')
            ->assertSee('Bilal Ahmed');

        // Admin Show
        $showResponse = $this->actingAs($this->admin)->get(route('admin.sale-returns.show', $saleReturn->id));
        $showResponse->assertOk()
            ->assertSee('RET-20260323-0001')
            ->assertSee('Customer Changed Mind');
    }

    public function test_dashboard_and_reports_deduct_returns_correctly(): void
    {
        // 1. Direct Sale: 2x Hammer Pro @ 700 = 1400 (Cost: 2x 400 = 800)
        $sale = Sale::create([
            'invoice_number' => 'INV-20260323-0099',
            'user_id'        => $this->cashier->id,
            'customer_name'  => 'Hamza Khan',
            'subtotal'       => 1400.00,
            'discount_amount'=> 0.00,
            'tax_amount'     => 0.00,
            'total_amount'   => 1400.00,
            'paid_amount'    => 1400.00,
            'change_amount'  => 0.00,
            'payment_method' => 'cash',
            'status'         => 'completed',
            'created_at'     => now(),
        ]);

        SaleItem::create([
            'sale_id'         => $sale->id,
            'product_id'      => $this->productA->id,
            'product_name'    => $this->productA->name,
            'product_sku'     => $this->productA->sku,
            'product_unit'    => $this->productA->unit,
            'quantity'        => 2,
            'cost_price'      => 400.00,
            'unit_price'      => 700.00,
            'discount_amount' => 0.00,
            'total_price'     => 1400.00,
        ]);

        // 2. Process return of 1x Hammer Pro @ 700 (Cost: 1x 400 = 400)
        $saleReturn = SaleReturn::create([
            'return_number'       => 'RET-20260323-0099',
            'sale_id'             => $sale->id,
            'customer_name'       => 'Hamza Khan',
            'user_id'             => $this->cashier->id,
            'total_return_amount' => 700.00,
            'refund_amount'       => 700.00,
            'refund_method'       => 'cash',
            'refund_status'       => 'completed',
            'returned_at'         => now()->toDateString(),
            'created_at'          => now(),
        ]);

        \App\Models\SaleReturnItem::create([
            'sale_return_id' => $saleReturn->id,
            'product_id'     => $this->productA->id,
            'product_name'   => $this->productA->name,
            'product_sku'    => $this->productA->sku,
            'product_unit'   => $this->productA->unit,
            'quantity'       => 1,
            'unit_price'     => 700.00,
            'total_price'    => 700.00,
        ]);

        // 3. Test Dashboard calculations:
        // Gross Sales: 1400, Return: 700 => Net Sales: 700
        // Gross COGS: 800, Returned COGS: 400 => Net COGS: 400
        // Gross Profit: 700 - 400 = 300
        $dashResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashResponse->assertOk()
            ->assertViewHas('todaySales', 700.00)
            ->assertViewHas('todayCogs', 400.00)
            ->assertViewHas('todayGrossProfit', 300.00);

        // 4. Test Reports Daily Sales Tab
        $reportResponse = $this->actingAs($this->admin)->get(route('admin.reports.index', ['tab' => 'daily']));
        $reportResponse->assertOk()
            ->assertSee('Daily Sales')
            ->assertSee('Returns / Refunds');
    }
}
