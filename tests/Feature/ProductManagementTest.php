<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'Store Administrator',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name'      => 'Store Cashier',
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        $this->category = Category::firstOrCreate(
            ['slug' => 'sanitary'],
            ['name' => 'Sanitary', 'is_active' => true]
        );

        $this->product = Product::create([
            'category_id'         => $this->category->id,
            'name'                => 'Master Basin Mixer Chrome',
            'slug'                => 'master-basin-mixer-chrome',
            'sku'                 => 'MBM-101',
            'barcode'             => '8964000101',
            'description'         => 'High quality brass body basin mixer',
            'unit'                => 'pc',
            'cost_price'          => 4500.00,
            'sale_price'          => 6200.00,
            'stock_quantity'      => 30,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);
    }

    public function test_product_create_and_edit_forms_render_with_rupee_symbols(): void
    {
        // 1. Create page renders with Rs.
        $createResponse = $this->actingAs($this->admin)->get(route('admin.products.create'));
        $createResponse->assertOk()
            ->assertSee('bi-currency-rupee')
            ->assertSee('Rs.');

        // 2. Edit page renders with Rs.
        $editResponse = $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product));
        $editResponse->assertOk()
            ->assertSee('bi-currency-rupee')
            ->assertSee('Rs.')
            ->assertSee('Admin privilege: You can modify the stock quantity directly.');
    }

    public function test_admin_can_update_product_opening_stock_quantity_increase(): void
    {
        $payload = [
            'name'                => 'Master Basin Mixer Chrome Updated',
            'category_id'         => $this->category->id,
            'sku'                 => 'MBM-101',
            'barcode'             => '8964000101',
            'description'         => 'Updated description',
            'unit'                => 'pc',
            'cost_price'          => 4600.00,
            'sale_price'          => 6500.00,
            'stock_quantity'      => 45, // increased from 30 to 45
            'low_stock_threshold' => 8,
            'is_active'           => 1,
            'show_in_catalog'     => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $this->product), $payload);

        $response->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(45, $this->product->stock_quantity);
        $this->assertEquals(4600.00, (float)$this->product->cost_price);
        $this->assertEquals(6500.00, (float)$this->product->sale_price);

        // Verify audit StockMovement logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id'   => $this->product->id,
            'type'         => 'adjustment_in',
            'quantity'     => 15,
            'stock_before' => 30,
            'stock_after'  => 45,
            'user_id'      => $this->admin->id,
        ]);
    }

    public function test_admin_can_update_product_opening_stock_quantity_decrease(): void
    {
        $payload = [
            'name'                => $this->product->name,
            'category_id'         => $this->category->id,
            'sku'                 => $this->product->sku,
            'unit'                => $this->product->unit,
            'cost_price'          => $this->product->cost_price,
            'sale_price'          => $this->product->sale_price,
            'stock_quantity'      => 20, // decreased from 30 to 20
            'low_stock_threshold' => 5,
            'is_active'           => 1,
            'show_in_catalog'     => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $this->product), $payload);

        $response->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(20, $this->product->stock_quantity);

        // Verify audit StockMovement logged with adjustment_out
        $this->assertDatabaseHas('stock_movements', [
            'product_id'   => $this->product->id,
            'type'         => 'adjustment_out',
            'quantity'     => 10,
            'stock_before' => 30,
            'stock_after'  => 20,
            'user_id'      => $this->admin->id,
        ]);
    }

    public function test_updating_product_without_stock_change_does_not_create_stock_movement(): void
    {
        $payload = [
            'name'                => 'Master Basin Mixer Chrome Renamed',
            'category_id'         => $this->category->id,
            'sku'                 => $this->product->sku,
            'unit'                => $this->product->unit,
            'cost_price'          => $this->product->cost_price,
            'sale_price'          => $this->product->sale_price,
            'stock_quantity'      => 30, // unchanged
            'low_stock_threshold' => 5,
            'is_active'           => 1,
            'show_in_catalog'     => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $this->product), $payload);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertEquals(0, StockMovement::where('product_id', $this->product->id)->count());
    }

    public function test_product_displays_supplier_info_and_supports_supplier_filtering(): void
    {
        $supplierA = \App\Models\Supplier::create([
            'name'            => 'Master Sanitary Fittings Ltd',
            'phone'           => '051-998877',
            'opening_balance' => 0,
            'is_active'       => true,
        ]);

        $supplierB = \App\Models\Supplier::create([
            'name'            => 'Super Pipe Mills',
            'phone'           => '051-443322',
            'opening_balance' => 0,
            'is_active'       => true,
        ]);

        // Assign supplierA to product
        $this->product->update(['supplier_id' => $supplierA->id]);

        $productB = Product::create([
            'category_id'         => $this->category->id,
            'supplier_id'         => $supplierB->id,
            'name'                => 'PPRC Pipe 25mm 4Mtr',
            'slug'                => 'pprc-pipe-25mm-4mtr',
            'sku'                 => 'PPR-025',
            'unit'                => 'meter',
            'cost_price'          => 350.00,
            'sale_price'          => 500.00,
            'stock_quantity'      => 100,
            'low_stock_threshold' => 10,
            'is_active'           => true,
        ]);

        // 1. Products index renders supplier column with supplier name
        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $response->assertOk()
            ->assertSee('Master Sanitary Fittings Ltd')
            ->assertSee('Super Pipe Mills');

        // 2. Filter by supplierA
        $responseFilterA = $this->actingAs($this->admin)->get(route('admin.products.index', ['supplier_id' => $supplierA->id]));
        $responseFilterA->assertOk()
            ->assertSee('Master Basin Mixer Chrome')
            ->assertDontSee('PPRC Pipe 25mm 4Mtr');

        // 3. Filter by supplierB
        $responseFilterB = $this->actingAs($this->admin)->get(route('admin.products.index', ['supplier_id' => $supplierB->id]));
        $responseFilterB->assertOk()
            ->assertSee('PPRC Pipe 25mm 4Mtr')
            ->assertDontSee('Master Basin Mixer Chrome');
    }

    public function test_admin_can_create_product_with_pasted_base64_image(): void
    {
        // 1x1 transparent PNG base64
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $payload = [
            'category_id'         => $this->category->id,
            'name'                => 'Pasted Image Shower Head',
            'sku'                 => 'SHW-PASTE-01',
            'unit'                => 'pc',
            'cost_price'          => 1200,
            'sale_price'          => 1800,
            'stock_quantity'      => 15,
            'low_stock_threshold' => 1,
            'image_base64'        => $base64Image,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'SHW-PASTE-01')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        $this->assertStringStartsWith('products/', $product->image);
    }

    public function test_updating_product_preserves_page_and_query_parameters(): void
    {
        $queryParams = ['page' => '3', 'search' => 'Mixer', 'category_id' => (string)$this->category->id];

        // 1. Edit view includes query parameters in form action and back button
        $editUrl = route('admin.products.edit', array_merge(['product' => $this->product->id], $queryParams));
        $response = $this->actingAs($this->admin)->get($editUrl);
        $response->assertOk()
            ->assertSee('page=3')
            ->assertSee('search=Mixer');

        // 2. Submitting update with query parameters redirects to index on page 3 with query params
        $updateUrl = route('admin.products.update', array_merge(['product' => $this->product->id], $queryParams));
        $payload = [
            'name'                => 'Master Basin Mixer Chrome Updated',
            'category_id'         => $this->category->id,
            'sku'                 => $this->product->sku,
            'unit'                => $this->product->unit,
            'cost_price'          => $this->product->cost_price,
            'sale_price'          => $this->product->sale_price,
            'stock_quantity'      => $this->product->stock_quantity,
            'low_stock_threshold' => 1,
            'is_active'           => 1,
            'show_in_catalog'     => 1,
        ];

        $updateResponse = $this->actingAs($this->admin)->put($updateUrl, $payload);
        $updateResponse->assertRedirect(route('admin.products.index', $queryParams));

        // 3. Toggle status preserves query parameters
        $toggleUrl = route('admin.products.toggle-status', array_merge(['product' => $this->product->id], $queryParams));
        $toggleResponse = $this->actingAs($this->admin)->patch($toggleUrl);
        $toggleResponse->assertRedirect(route('admin.products.index', $queryParams));
    }
}
