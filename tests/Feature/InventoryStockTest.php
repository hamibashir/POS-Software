<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'sanitary'],
            ['name' => 'Sanitary', 'is_active' => true]
        );

        $this->product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'PPRC Pipe 25mm',
            'slug'                => 'pprc-pipe-25mm',
            'sku'                 => 'PPR-025',
            'unit'                => 'length',
            'cost_price'          => 400.00,
            'sale_price'          => 600.00,
            'stock_quantity'      => 50,
            'low_stock_threshold' => 15,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);
    }

    public function test_admin_can_perform_manual_stock_adjustment_in(): void
    {
        // Add 20 lengths
        $payload = [
            'product_id' => $this->product->id,
            'type'       => 'adjustment_in',
            'quantity'   => 20,
            'notes'      => 'Found extra inventory during annual count',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.stock.store'), $payload);

        $response->assertRedirect(route('admin.stock.index'));

        $this->product->refresh();
        $this->assertEquals(70, $this->product->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'   => $this->product->id,
            'type'         => 'adjustment_in',
            'quantity'     => 20,
            'stock_before' => 50,
            'stock_after'  => 70,
        ]);
    }

    public function test_admin_can_perform_manual_stock_adjustment_out(): void
    {
        // Remove 10 damaged lengths
        $payload = [
            'product_id' => $this->product->id,
            'type'       => 'adjustment_out',
            'quantity'   => 10,
            'notes'      => 'Damaged during unloading',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.stock.store'), $payload);

        $response->assertRedirect(route('admin.stock.index'));

        $this->product->refresh();
        $this->assertEquals(40, $this->product->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'   => $this->product->id,
            'type'         => 'adjustment_out',
            'quantity'     => 10,
            'stock_before' => 50,
            'stock_after'  => 40,
        ]);
    }

    public function test_product_low_stock_helpers_and_scopes(): void
    {
        // 50 stock with threshold 15 -> not low stock
        $this->assertFalse($this->product->isLowStock());
        $this->assertFalse($this->product->isOutOfStock());
        $this->assertEquals('in_stock', $this->product->stock_status);

        // Update stock to 10 -> low stock
        $this->product->update(['stock_quantity' => 10]);
        $this->assertTrue($this->product->isLowStock());
        $this->assertEquals('low_stock', $this->product->stock_status);

        // Update stock to 0 -> out of stock
        $this->product->update(['stock_quantity' => 0]);
        $this->assertTrue($this->product->isOutOfStock());
        $this->assertEquals('out_of_stock', $this->product->stock_status);
    }
}
