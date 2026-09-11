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

        $this->categoryTools = Category::create([
            'name'      => 'Power Tools',
            'slug'      => 'power-tools',
            'is_active' => true,
        ]);

        $this->categoryPipes = Category::create([
            'name'      => 'Pipes & Fittings',
            'slug'      => 'pipes-fittings',
            'is_active' => true,
        ]);

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
            ->assertSee('15,500'); // 12,000 + 3,500
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
            ->assertSee('12,000') // cash
            ->assertSee('3,500');  // card
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

    public function test_top_selling_tab_groups_by_category_and_calculates_units(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['tab' => 'topsell']));

        $response->assertOk()
            ->assertSee('Category-Wise Sales Summary')
            ->assertSee('Power Tools')
            ->assertSee('Pipes & Fittings')
            ->assertSee('UPVC Bend 4 inch')
            ->assertSee('10'); // 10 units sold
    }
}
