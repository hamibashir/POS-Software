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

class PosReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Product $product;
    protected Sale $sale;
    protected SaleItem $saleItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'sanitary'],
            ['name' => 'Sanitary', 'is_active' => true]
        );

        $this->product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Chrome Basin Mixer',
            'slug'                => 'chrome-basin-mixer',
            'sku'                 => 'MIX-001',
            'barcode'             => '5551234567',
            'unit'                => 'pc',
            'cost_price'          => 2000.00,
            'sale_price'          => 3500.00,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 2,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        // Create an initial completed sale: 2 units sold at 3500 each = 7000
        $this->sale = Sale::create([
            'invoice_number'  => 'INV-20260311-0001',
            'user_id'         => $this->cashier->id,
            'customer_name'   => 'Customer ABC',
            'subtotal'        => 7000.00,
            'discount_amount' => 0.00,
            'tax_amount'      => 0.00,
            'total_amount'    => 7000.00,
            'paid_amount'     => 7000.00,
            'change_amount'   => 0.00,
            'payment_method'  => 'cash',
            'status'          => 'completed',
        ]);

        $this->saleItem = SaleItem::create([
            'sale_id'         => $this->sale->id,
            'product_id'      => $this->product->id,
            'product_name'    => $this->product->name,
            'product_sku'     => $this->product->sku,
            'product_unit'    => $this->product->unit,
            'quantity'        => 2,
            'unit_price'      => 3500.00,
            'cost_price'      => 2000.00,
            'discount_amount' => 0.00,
            'total_price'     => 7000.00,
        ]);
    }

    public function test_lookup_sale_by_invoice_number(): void
    {
        $response = $this->actingAs($this->cashier)
            ->getJson(route('cashier.pos.lookup-sale', ['invoice' => $this->sale->invoice_number]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sale.invoice_number', $this->sale->invoice_number)
            ->assertJsonPath('sale.items.0.product_name', 'Chrome Basin Mixer')
            ->assertJsonPath('sale.items.0.unit_price', 3500)
            ->assertJsonPath('sale.items.0.quantity_returnable', 2);
    }

    public function test_return_against_sale_restores_stock_and_records_movement(): void
    {
        $initialStock = $this->product->stock_quantity; // 10

        $payload = [
            'sale_id'        => $this->sale->id,
            'refund_method'  => 'cash',
            'refund_amount'  => 3500.00,
            'customer_name'  => 'Customer ABC',
            'reason'         => 'Defective unit',
            'items'          => [
                [
                    'sale_item_id' => $this->saleItem->id,
                    'product_id'   => $this->product->id,
                    'quantity'     => 1,
                    'unit_price'   => 3500.00,
                    'reason'       => 'Defective unit',
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.process-return'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verify return record created
        $return = SaleReturn::latest('id')->first();
        $this->assertNotNull($return);
        $this->assertEquals(3500.00, (float)$return->total_return_amount);
        $this->assertEquals(3500.00, (float)$return->refund_amount);
        $this->assertEquals($this->sale->id, $return->sale_id);

        // Verify stock restored: 10 + 1 = 11
        $this->product->refresh();
        $this->assertEquals($initialStock + 1, $this->product->stock_quantity);

        // Verify stock movement logged
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('type', 'return')
            ->latest('id')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(1, $movement->quantity);
    }

    public function test_direct_product_return_without_invoice(): void
    {
        $initialStock = $this->product->stock_quantity;

        $payload = [
            'refund_method'  => 'cash',
            'refund_amount'  => 7000.00,
            'customer_name'  => 'Walk-in Return',
            'reason'         => 'Customer changed mind',
            'items'          => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 2,
                    'unit_price' => 3500.00,
                    'reason'     => 'Customer changed mind',
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.process-return'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $return = SaleReturn::latest('id')->first();
        $this->assertNotNull($return);
        $this->assertNull($return->sale_id);
        $this->assertEquals(7000.00, (float)$return->total_return_amount);
        $this->assertEquals(7000.00, (float)$return->refund_amount);

        // Stock restored: 10 + 2 = 12
        $this->product->refresh();
        $this->assertEquals($initialStock + 2, $this->product->stock_quantity);
    }

    public function test_return_voucher_receipt_renders(): void
    {
        $payload = [
            'refund_method'  => 'cash',
            'refund_amount'  => 3500.00,
            'customer_name'  => 'Customer ABC',
            'items'          => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 1,
                    'unit_price' => 3500.00,
                ],
            ],
        ];

        $this->actingAs($this->cashier)
            ->postJson(route('cashier.pos.process-return'), $payload);

        $return = SaleReturn::latest('id')->first();

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.pos.return-receipt', $return->id));

        $response->assertOk()
            ->assertSee($return->return_number)
            ->assertSee('Chrome Basin Mixer')
            ->assertSee('Hassan & Sons');
    }
}
