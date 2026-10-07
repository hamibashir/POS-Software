<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Supplier $supplier;
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

        $this->supplier = Supplier::create([
            'name'            => 'Apex Hardware Importers',
            'company_name'    => 'Apex Corp',
            'phone'           => '051-1234567',
            'opening_balance' => 5000.00,
            'is_active'       => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'hardware'],
            ['name' => 'Hardware', 'is_active' => true]
        );

        $this->product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'High Tensile Bolts',
            'slug'                => 'high-tensile-bolts',
            'sku'                 => 'BLT-001',
            'unit'                => 'box',
            'cost_price'          => 1000.00,
            'sale_price'          => 1500.00,
            'stock_quantity'      => 20,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);
    }

    public function test_purchase_creation_updates_stock_and_supplier_balance(): void
    {
        // Initial supplier balance = 5000 (opening)
        // Purchase 10 boxes @ 1000 = 10,000, Paid 4,000, Remaining = 6,000
        // New expected pending balance = 5000 + 6000 = 11,000
        $payload = [
            'supplier_id'     => $this->supplier->id,
            'supplier_name'   => $this->supplier->name,
            'payment_method'  => 'cash',
            'paid_amount'     => 4000.00,
            'items'           => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 10,
                    'unit_cost'  => 1000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.purchases.store'), $payload);

        $purchase = Purchase::latest('id')->first();
        $this->assertNotNull($purchase);
        $response->assertRedirect(route('admin.purchases.show', $purchase));

        // Stock should increase from 20 to 30
        $this->product->refresh();
        $this->assertEquals(30, $this->product->stock_quantity);

        // Supplier balance check
        $this->supplier->refresh();
        $this->assertEquals(11000.00, (float)$this->supplier->pending_balance);
    }

    public function test_cashier_can_record_supplier_payment_at_pos_counter(): void
    {
        // Initial pending balance = 5000
        // Pay 3000 at POS
        // New balance should be 2000
        $payload = [
            'supplier_id'      => $this->supplier->id,
            'amount'           => 3000.00,
            'payment_method'   => 'cash',
            'reference_number' => 'POS-REC-001',
            'notes'            => 'Cash cleared at POS counter',
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.supplier-payments'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('raw_new_balance', 2000);

        $this->supplier->refresh();
        $this->assertEquals(2000.00, (float)$this->supplier->pending_balance);

        $this->assertDatabaseHas('supplier_payments', [
            'supplier_id'      => $this->supplier->id,
            'amount'           => 3000.00,
            'payment_method'   => 'cash',
            'reference_number' => 'POS-REC-001',
        ]);
    }

    public function test_supplier_index_displays_paid_amount_in_stats_and_table(): void
    {
        SupplierPayment::create([
            'supplier_id'      => $this->supplier->id,
            'user_id'          => $this->admin->id,
            'amount'           => 3500.00,
            'payment_method'   => 'bank',
            'reference_number' => 'VCH-00099',
            'payment_date'     => today(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.suppliers.index'));

        $response->assertOk()
            ->assertSee('Total Paid Amount')
            ->assertSee('3,500.00')
            ->assertSee('1 payment(s)');
    }

    public function test_admin_can_view_supplier_ledger(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.suppliers.ledger', $this->supplier->id));

        $response->assertOk()
            ->assertSee('Apex Hardware Importers')
            ->assertSee('5,000');
    }

    public function test_pos_can_fetch_supplier_low_stock_items(): void
    {
        // 1. Create a purchase from this supplier for $this->product
        $purchase = Purchase::create([
            'reference_number' => 'PUR-TEST-001',
            'user_id'          => $this->admin->id,
            'supplier_id'      => $this->supplier->id,
            'supplier_name'    => $this->supplier->name,
            'payment_method'   => 'cash',
            'total_amount'     => 1000,
            'paid_amount'      => 1000,
            'status'           => 'completed',
            'received_at'      => now(),
        ]);

        PurchaseItem::create([
            'purchase_id'  => $purchase->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'product_unit' => $this->product->unit,
            'quantity'     => 10,
            'unit_cost'    => 1000,
            'total_cost'   => 10000,
        ]);

        // When stock is normal (20 > 5), low stock count is 0
        $response1 = $this->actingAs($this->cashier)
            ->getJson(route('cashier.pos.supplier-low-stock', $this->supplier->id));

        $response1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 0);

        // When stock is reduced to low stock (3 <= 5)
        $this->product->update(['stock_quantity' => 3]);

        $response2 = $this->actingAs($this->cashier)
            ->getJson(route('cashier.pos.supplier-low-stock', $this->supplier->id));

        $response2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.name', 'High Tensile Bolts')
            ->assertJsonPath('items.0.stock_quantity', 3)
            ->assertJsonPath('items.0.is_out_of_stock', false);

        // Also test a product assigned directly via supplier_id without purchase history
        $directProduct = Product::create([
            'category_id'         => $this->product->category_id,
            'supplier_id'         => $this->supplier->id,
            'name'                => 'Direct Supplier Nut M8',
            'sku'                 => 'NUT-M8',
            'cost_price'          => 50,
            'sale_price'          => 80,
            'stock_quantity'      => 0,
            'low_stock_threshold' => 10,
            'unit'                => 'pcs',
            'is_active'           => true,
        ]);

        $response3 = $this->actingAs($this->cashier)
            ->getJson(route('cashier.pos.supplier-low-stock', $this->supplier->id));

        $response3->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 2);
    }

    public function test_admin_can_edit_supplier_payment(): void
    {
        $payment = SupplierPayment::create([
            'supplier_id'      => $this->supplier->id,
            'user_id'          => $this->admin->id,
            'amount'           => 1000.00,
            'payment_method'   => 'cash',
            'payment_date'     => today(),
            'reference_number' => 'PAY-1',
            'notes'            => 'Initial partial payment',
        ]);

        $this->assertEquals(4000.00, (float)$this->supplier->fresh()->pending_balance);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.suppliers.payments.update', $payment), [
                'amount'           => 2500.00,
                'payment_method'   => 'bank',
                'payment_date'     => today()->format('Y-m-d'),
                'reference_number' => 'BANK-TRX-999',
                'notes'            => 'Updated payment amount',
            ]);

        $response->assertRedirect();
        $this->assertEquals(2500.00, (float)$this->supplier->fresh()->pending_balance);

        $payment->refresh();
        $this->assertEquals(2500.00, (float)$payment->amount);
        $this->assertEquals('bank', $payment->payment_method);
        $this->assertEquals('BANK-TRX-999', $payment->reference_number);
    }

    public function test_admin_can_delete_supplier_payment(): void
    {
        $payment = SupplierPayment::create([
            'supplier_id'      => $this->supplier->id,
            'user_id'          => $this->admin->id,
            'amount'           => 2000.00,
            'payment_method'   => 'cash',
            'payment_date'     => today(),
        ]);

        $this->assertEquals(3000.00, (float)$this->supplier->fresh()->pending_balance);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.suppliers.payments.destroy', $payment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('supplier_payments', ['id' => $payment->id]);
        $this->assertEquals(5000.00, (float)$this->supplier->fresh()->pending_balance);
    }

    public function test_admin_can_edit_purchase_and_inventory_stock_reconciles(): void
    {
        // Initial stock = 20
        // Purchase 10 units => stock = 30
        $purchase = Purchase::create([
            'reference_number' => 'PO-20261007-0001',
            'user_id'          => $this->admin->id,
            'supplier_id'      => $this->supplier->id,
            'supplier_name'    => $this->supplier->name,
            'total_amount'     => 10000.00,
            'paid_amount'      => 0.00,
            'payment_method'   => 'credit',
            'status'           => 'received',
            'received_at'      => now(),
        ]);

        PurchaseItem::create([
            'purchase_id'    => $purchase->id,
            'product_id'     => $this->product->id,
            'product_name'   => $this->product->name,
            'product_sku'    => $this->product->sku,
            'product_unit'   => $this->product->unit,
            'quantity'       => 10,
            'unit_cost'      => 1000.00,
            'total_cost'     => 10000.00,
        ]);
        $this->product->increment('stock_quantity', 10);
        $this->assertEquals(30, $this->product->fresh()->stock_quantity);

        // Edit purchase to 15 units instead of 10
        $response = $this->actingAs($this->admin)
            ->put(route('admin.purchases.update', $purchase), [
                'supplier_id'    => $this->supplier->id,
                'supplier_name'  => $this->supplier->name,
                'payment_method' => 'credit',
                'items'          => [
                    [
                        'product_id' => $this->product->id,
                        'quantity'   => 15,
                        'unit_cost'  => 1000.00,
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.purchases.show', $purchase));

        // Stock should now be 20 + 15 = 35
        $this->assertEquals(35, $this->product->fresh()->stock_quantity);
        $this->assertEquals(15000.00, (float)$purchase->fresh()->total_amount);
    }

    public function test_admin_can_delete_purchase_and_inventory_stock_reverts(): void
    {
        // Initial stock = 20
        $purchase = Purchase::create([
            'reference_number' => 'PO-20261007-0002',
            'user_id'          => $this->admin->id,
            'supplier_id'      => $this->supplier->id,
            'supplier_name'    => $this->supplier->name,
            'total_amount'     => 5000.00,
            'paid_amount'      => 5000.00,
            'payment_method'   => 'cash',
            'status'           => 'received',
            'received_at'      => now(),
        ]);

        PurchaseItem::create([
            'purchase_id'    => $purchase->id,
            'product_id'     => $this->product->id,
            'product_name'   => $this->product->name,
            'product_sku'    => $this->product->sku,
            'product_unit'   => $this->product->unit,
            'quantity'       => 5,
            'unit_cost'      => 1000.00,
            'total_cost'     => 5000.00,
        ]);
        $this->product->increment('stock_quantity', 5);
        $this->assertEquals(25, $this->product->fresh()->stock_quantity);

        SupplierPayment::create([
            'supplier_id'      => $this->supplier->id,
            'purchase_id'      => $purchase->id,
            'user_id'          => $this->admin->id,
            'amount'           => 5000.00,
            'payment_method'   => 'cash',
            'payment_date'     => today(),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.purchases.destroy', $purchase));

        $response->assertRedirect(route('admin.purchases.index'));

        // Stock should revert back to 20
        $this->assertEquals(20, $this->product->fresh()->stock_quantity);
        $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
        $this->assertDatabaseMissing('supplier_payments', ['purchase_id' => $purchase->id]);
    }

    public function test_purchase_create_preselects_supplier_from_query_parameter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.purchases.create', ['supplier_id' => $this->supplier->id]));

        $response->assertOk()
            ->assertSee('Apex Hardware Importers')
            ->assertSee('value="' . $this->supplier->id . '"', false);
    }
}
