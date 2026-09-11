<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
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

        $category = Category::create([
            'name'      => 'Sanitary',
            'slug'      => 'sanitary',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Luxury Sink Tap',
            'slug'                => 'luxury-sink-tap',
            'sku'                 => 'SNK-001',
            'unit'                => 'pc',
            'cost_price'          => 1500.00,
            'sale_price'          => 2800.00,
            'stock_quantity'      => 25,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);
    }

    public function test_cashier_cannot_access_admin_dashboard_or_staff(): void
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('cashier.pos'))
            ->assertSessionHas('error');

        $responseStaff = $this->actingAs($this->cashier)
            ->get(route('admin.staff.index'));

        $responseStaff->assertRedirect(route('cashier.pos'))
            ->assertSessionHas('error');
    }

    public function test_admin_can_access_dashboard_and_cashier_pos(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();

        $responsePos = $this->actingAs($this->admin)
            ->get(route('cashier.pos'));

        $responsePos->assertOk();
    }

    public function test_public_catalog_home_renders_with_phone_cta_and_brand(): void
    {
        $response = $this->get(route('catalog.home'));

        $response->assertOk()
            ->assertSee('Hassan & Sons')
            ->assertSee('051-8891930')
            ->assertSee('Order by Phone');
    }

    public function test_public_catalog_product_details_renders_with_phone_modal_trigger(): void
    {
        $response = $this->get(route('catalog.product', $this->product->slug));

        $response->assertOk()
            ->assertSee('Luxury Sink Tap')
            ->assertSee('051-8891930')
            ->assertSee('Call to Order (051-8891930)');
    }
}
