<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemIntegrityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;

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
    }

    /**
     * Audit: Check that all Admin GET routes render cleanly with HTTP 200 without throwing 500 errors.
     */
    public function test_all_admin_get_routes_render_cleanly_for_admin(): void
    {
        $category = Category::create(['name' => 'General Hardware', 'slug' => 'general-hardware', 'is_active' => true]);
        $product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Steel Screws 100pk',
            'slug'                => 'steel-screws-100pk',
            'sku'                 => 'SCR-100',
            'unit'                => 'box',
            'cost_price'          => 100.00,
            'sale_price'          => 180.00,
            'stock_quantity'      => 50,
            'low_stock_threshold' => 10,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);
        $supplier = Supplier::create(['name' => 'National Tools Ltd', 'is_active' => true]);
        $employee = Employee::create(['name' => 'Babar Azam', 'phone' => '03001112233', 'address' => 'Rawalpindi', 'is_active' => true]);

        $sale = Sale::create([
            'invoice_number' => 'INV-20260311-0001',
            'user_id'        => $this->admin->id,
            'subtotal'       => 180.00,
            'total_amount'   => 180.00,
            'paid_amount'    => 180.00,
            'change_amount'  => 0.00,
            'status'         => 'completed',
        ]);

        $purchase = Purchase::create([
            'reference_number' => 'PUR-20260311-0001',
            'supplier_id'      => $supplier->id,
            'supplier_name'    => $supplier->name,
            'user_id'          => $this->admin->id,
            'total_amount'     => 5000.00,
            'paid_amount'      => 5000.00,
            'received_at'      => now()->toDateString(),
            'status'           => 'completed',
        ]);

        $getRoutes = [
            route('admin.dashboard'),
            route('admin.categories.index'),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.products.show', $product->id),
            route('admin.products.edit', $product->id),
            route('admin.sales.index'),
            route('admin.sales.show', $sale->id),
            route('admin.purchases.index'),
            route('admin.purchases.create'),
            route('admin.purchases.show', $purchase->id),
            route('admin.purchase-returns.index'),
            route('admin.purchase-returns.create'),
            route('admin.stock.index'),
            route('admin.stock.create'),
            route('admin.reports.index', ['tab' => 'daily']),
            route('admin.reports.index', ['tab' => 'range']),
            route('admin.reports.index', ['tab' => 'lowstock']),
            route('admin.reports.index', ['tab' => 'topsell']),
            route('admin.expenses.index'),
            route('admin.suppliers.index'),
            route('admin.suppliers.ledger', $supplier->id),
            route('admin.staff.index'),
            route('admin.staff.employees.ledger', $employee->id),
        ];

        foreach ($getRoutes as $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Route {$url} failed with status {$response->status()}"
            );
        }
    }

    /**
     * Audit: Check complex multi-item financial math with fractional prices and line item discounts.
     */
    public function test_financial_precision_under_complex_multi_item_discounts(): void
    {
        $category = Category::create(['name' => 'Fasteners', 'slug' => 'fasteners', 'is_active' => true]);
        $prod1 = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Item 1',
            'sku'                 => 'ITM-001',
            'unit'                => 'pc',
            'cost_price'          => 12.35,
            'sale_price'          => 19.99,
            'stock_quantity'      => 100,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        $prod2 = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Item 2',
            'sku'                 => 'ITM-002',
            'unit'                => 'pc',
            'cost_price'          => 45.50,
            'sale_price'          => 75.25,
            'stock_quantity'      => 100,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        $payload = [
            'cart' => [
                [
                    'product_id'      => $prod1->id,
                    'quantity'        => 3, // 3 * 19.99 = 59.97, minus 4.97 discount = 55.00
                    'unit_price'      => 19.99,
                    'discount_amount' => 4.97,
                ],
                [
                    'product_id'      => $prod2->id,
                    'quantity'        => 2, // 2 * 75.25 = 150.50, minus 0.50 discount = 150.00
                    'unit_price'      => 75.25,
                    'discount_amount' => 0.50,
                ],
            ],
            'discount_amount' => 5.00, // Header discount: 55.00 + 150.00 = 205.00 - 5.00 = 200.00
            'payment_method'  => 'cash',
            'paid_amount'     => 500.00, // Paid 500 -> Change = 300.00
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.complete-sale'), $payload);

        $response->assertOk();

        $sale = Sale::latest('id')->first();
        $this->assertEquals(205.00, (float)$sale->subtotal);
        $this->assertEquals(5.00, (float)$sale->discount_amount);
        $this->assertEquals(200.00, (float)$sale->total_amount);
        $this->assertEquals(500.00, (float)$sale->paid_amount);
        $this->assertEquals(300.00, (float)$sale->change_amount);
    }

    /**
     * Audit: Check purchase return to supplier reduces stock and logs movement.
     */
    public function test_purchase_return_reduces_stock_and_logs_movement(): void
    {
        $category = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true]);
        $product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'PVC Pipe 10ft',
            'sku'                 => 'PVC-010',
            'unit'                => 'pc',
            'cost_price'          => 200.00,
            'sale_price'          => 300.00,
            'stock_quantity'      => 40,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        $supplier = Supplier::create(['name' => 'PVC Master', 'phone' => '03112223344', 'is_active' => true]);

        $payload = [
            'supplier_name'  => $supplier->name,
            'supplier_phone' => $supplier->phone,
            'refund_method'  => 'cash',
            'refund_status'  => 'completed',
            'refund_amount'  => 1000.00,
            'returned_at'    => now()->toDateString(),
            'reason'         => 'Damaged batch',
            'items'          => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 5,
                    'unit_cost'  => 200.00,
                    'reason'     => 'Damaged',
                ]
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.purchase-returns.store'), $payload);
        $response->assertSessionHasNoErrors();

        // Check stock reduced from 40 to 35
        $this->assertEquals(35, $product->fresh()->stock_quantity);

        // Check StockMovement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => 'return',
            'quantity'   => -5,
        ]);
    }

    /**
     * Audit: Check daily expense lifecycle (store, update, delete).
     */
    public function test_daily_expense_crud_lifecycle(): void
    {
        // 1. Store
        $response = $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'category'       => 'utilities',
            'title'          => 'Electricity Bill',
            'amount'         => 4500.00,
            'expense_date'   => now()->toDateString(),
            'payment_method' => 'cash',
            'notes'          => 'Store electricity bill paid via cash',
        ]);
        $response->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::latest('id')->first();
        $this->assertEquals('Electricity Bill', $expense->title);
        $this->assertEquals(4500.00, (float)$expense->amount);

        // 2. Update
        $response = $this->actingAs($this->admin)->from(route('admin.expenses.index'))->put(route('admin.expenses.update', $expense), [
            'category'       => 'utilities',
            'title'          => 'Electricity Bill - Revised',
            'amount'         => 4200.00,
            'expense_date'   => now()->toDateString(),
            'payment_method' => 'cash',
        ]);
        $response->assertRedirect(route('admin.expenses.index'));
        $this->assertEquals(4200.00, (float)$expense->fresh()->amount);

        // 3. Destroy
        $response = $this->actingAs($this->admin)->from(route('admin.expenses.index'))->delete(route('admin.expenses.destroy', $expense));
        $response->assertRedirect(route('admin.expenses.index'));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    /**
     * Audit: Check all report tabs render gracefully when there are 0 sales or zero records.
     */
    public function test_reports_tabs_render_cleanly_on_empty_dataset(): void
    {
        $tabs = ['daily', 'range', 'lowstock', 'topsell'];
        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->admin)->get(route('admin.reports.index', ['tab' => $tab]));
            $response->assertOk();
        }
    }
}
