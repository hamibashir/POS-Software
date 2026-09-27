<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $categoryTools;
    protected Category $categoryPipes;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->categoryTools = Category::firstOrCreate(
            ['slug' => 'electric-tools'],
            ['name' => 'Electric Tools', 'is_active' => true]
        );

        $this->categoryPipes = Category::firstOrCreate(
            ['slug' => 'sanitary'],
            ['name' => 'Sanitary', 'is_active' => true]
        );

        $this->productA = Product::create([
            'category_id'         => $this->categoryTools->id,
            'name'                => 'Cordless Drill 18V',
            'slug'                => 'cordless-drill-18v',
            'sku'                 => 'DRL-018',
            'unit'                => 'pc',
            'cost_price'          => 8000.00,
            'sale_price'          => 12000.00,
            'stock_quantity'      => 4, // Low stock
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        $this->productB = Product::create([
            'category_id'         => $this->categoryPipes->id,
            'name'                => 'UPVC Bend 4 inch',
            'slug'                => 'upvc-bend-4-inch',
            'sku'                 => 'BND-004',
            'unit'                => 'pc',
            'cost_price'          => 200.00,
            'sale_price'          => 350.00,
            'stock_quantity'      => 0, // Out of stock
            'low_stock_threshold' => 10,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        // Sale 1: Drill (1 unit @ 12,000)
        $sale1 = Sale::create([
            'invoice_number'  => 'INV-20260311-0001',
            'user_id'         => $this->admin->id,
            'subtotal'        => 12000.00,
            'discount_amount' => 0.00,
            'tax_amount'      => 0.00,
            'total_amount'    => 12000.00,
            'paid_amount'     => 12000.00,
            'change_amount'   => 0.00,
            'payment_method'  => 'cash',
            'status'          => 'completed',
        ]);

        SaleItem::create([
            'sale_id'         => $sale1->id,
            'product_id'      => $this->productA->id,
            'product_name'    => $this->productA->name,
            'product_sku'     => $this->productA->sku,
            'product_unit'    => $this->productA->unit,
            'quantity'        => 1,
            'unit_price'      => 12000.00,
            'cost_price'      => 8000.00,
            'discount_amount' => 0.00,
            'total_price'     => 12000.00,
        ]);

        // Sale 2: UPVC Bend (10 units @ 350 = 3500)
        $sale2 = Sale::create([
            'invoice_number'  => 'INV-20260311-0002',
            'user_id'         => $this->admin->id,
            'subtotal'        => 3500.00,
            'discount_amount' => 0.00,
            'tax_amount'      => 0.00,
            'total_amount'    => 3500.00,
            'paid_amount'     => 3500.00,
            'change_amount'   => 0.00,
            'payment_method'  => 'card',
            'status'          => 'completed',
        ]);

        SaleItem::create([
            'sale_id'         => $sale2->id,
            'product_id'      => $this->productB->id,
            'product_name'    => $this->productB->name,
            'product_sku'     => $this->productB->sku,
            'product_unit'    => $this->productB->unit,
            'quantity'        => 10,
            'unit_price'      => 350.00,
            'cost_price'      => 200.00,
            'discount_amount' => 0.00,
            'total_price'     => 3500.00,
        ]);
    }

    public function test_daily_sales_tab_renders_and_calculates_totals(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'daily']));

        $response->assertOk()
            ->assertSee('Daily Sales')
            ->assertSee('15,500') // 12,000 + 3,500
            ->assertSee('10,000') // COGS: 8,000 + 2,000
            ->assertSee('5,500');  // Gross & Net Profit
    }

    public function test_range_sales_tab_renders_and_breaks_down_payments(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', [
                'tab'  => 'range',
                'from' => now()->startOfMonth()->toDateString(),
                'to'   => now()->toDateString(),
            ]));

        $response->assertOk()
            ->assertSee('15,500')
            ->assertSee('10,000') // COGS
            ->assertSee('5,500');  // Gross & Net Profit
    }

    public function test_low_stock_tab_detects_low_and_out_of_stock(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'lowstock']));

        $response->assertOk()
            ->assertSee('Cordless Drill 18V')
            ->assertSee('UPVC Bend 4 inch')
            ->assertSee('OUT'); // Out of stock badge
    }

    public function test_stock_valuation_tab_calculates_total_available_stock_and_shop_values(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'stock']));

        $response->assertOk()
            ->assertSee('Stock & Shop Valuation', false)
            ->assertSee('Total Available Stock')
            ->assertSee('Shop Cost Valuation')
            ->assertSee('Shop Retail Valuation')
            ->assertSee('Category-wise Stock Valuation Breakdown')
            ->assertSee('Detailed Product Stock & Shop Valuation', false);
    }

    public function test_top_selling_tab_groups_by_category_and_calculates_units(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'topsell']));

        $response->assertOk()
            ->assertSee('Category-Wise Sales')
            ->assertSee('Electric Tools')
            ->assertSee('Sanitary')
            ->assertSee('UPVC Bend 4 inch')
            ->assertSee('10'); // 10 units sold
    }

    public function test_dashboard_renders_profit_metrics(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee("Today's Net Profit", false)
            ->assertSee("Monthly Net Profit", false)
            ->assertSee('5,500');
    }

    public function test_cleared_credit_payment_counted_on_clearance_day_not_purchase_day(): void
    {
        $customer = \App\Models\Employee::create([
            'name'      => 'Credit Customer Test',
            'phone'     => '03001234567',
            'address'   => 'Test Address',
            'is_active' => true,
        ]);

        // Purchase on credit 5 days ago for 4,000 PKR (unpaid: paid_amount = 0)
        $pastDate = now()->subDays(5);
        $creditSale = Sale::create([
            'invoice_number'  => 'INV-CREDIT-001',
            'user_id'         => $this->admin->id,
            'employee_id'     => $customer->id,
            'subtotal'        => 4000.00,
            'discount_amount' => 0.00,
            'tax_amount'      => 0.00,
            'total_amount'    => 4000.00,
            'paid_amount'     => 0.00,
            'change_amount'   => 0.00,
            'payment_method'  => 'credit',
            'status'          => 'completed',
            'created_at'      => $pastDate,
        ]);

        // Today: Customer clears 4,000 PKR of the credit
        \App\Models\EmployeePayment::create([
            'employee_id'    => $customer->id,
            'user_id'        => $this->admin->id,
            'amount'         => 4000.00,
            'payment_method' => 'cash',
            'payment_date'   => today(),
        ]);

        // Today's total sales should include 15,500 (from setup) + 4,000 cleared credit = 19,500
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'daily']));

        $response->assertOk()
            ->assertSee('19,500'); // 15,500 + 4,000

        // Dashboard today sales should also be 19,500
        $dashResponse = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $dashResponse->assertOk()
            ->assertSee('19,500');
    }

    public function test_supplier_payments_tab_renders_with_metrics_and_logs(): void
    {
        $supplier = \App\Models\Supplier::create([
            'name'            => 'National Cables Ltd',
            'company_name'    => 'National Cables',
            'phone'           => '03219876543',
            'opening_balance' => 10000.00,
            'is_active'       => true,
        ]);

        $purchase = \App\Models\Purchase::create([
            'reference_number' => 'PUR-TEST-99',
            'user_id'          => $this->admin->id,
            'supplier_id'      => $supplier->id,
            'supplier_name'    => $supplier->name,
            'payment_method'   => 'bank',
            'total_amount'     => 25000.00,
            'paid_amount'      => 5000.00,
            'status'           => 'completed',
            'received_at'      => today(),
        ]);

        \App\Models\SupplierPayment::create([
            'supplier_id'      => $supplier->id,
            'purchase_id'      => $purchase->id,
            'user_id'          => $this->admin->id,
            'amount'           => 12000.00,
            'payment_method'   => 'bank',
            'reference_number' => 'VCH-BANK-001',
            'payment_date'     => today(),
            'notes'            => 'Advance clearing via bank transfer',
        ]);

        // Tab: Suppliers
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'suppliers']));

        $response->assertOk()
            ->assertSee('Total Purchases')
            ->assertSee('25,000.00')
            ->assertSee('Total Paid Amount')
            ->assertSee('12,000.00')
            ->assertSee('National Cables Ltd')
            ->assertSee('VCH-BANK-001')
            ->assertSee('Advance clearing via bank transfer');
    }

    public function test_daily_and_range_reports_include_supplier_paid_amount(): void
    {
        $supplier = \App\Models\Supplier::create([
            'name'            => 'Steel Works Co',
            'opening_balance' => 0,
            'is_active'       => true,
        ]);

        \App\Models\SupplierPayment::create([
            'supplier_id'      => $supplier->id,
            'user_id'          => $this->admin->id,
            'amount'           => 8500.00,
            'payment_method'   => 'cash',
            'reference_number' => 'VCH-8500',
            'payment_date'     => today(),
        ]);

        // Daily tab check
        $dailyResponse = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'daily']));

        $dailyResponse->assertOk()
            ->assertSee('Supplier Paid')
            ->assertSee('8,500.00');

        // Range tab check
        $rangeResponse = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', [
                'tab'  => 'range',
                'from' => today()->toDateString(),
                'to'   => today()->toDateString(),
            ]));

        $rangeResponse->assertOk()
            ->assertSee('Supplier Paid Amount')
            ->assertSee('8,500.00');
    }
}
