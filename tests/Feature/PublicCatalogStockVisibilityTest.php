<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogStockVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_in_stock_products_appear_in_public_catalog_and_reappear_when_restocked(): void
    {
        $category = Category::create([
            'name'      => 'Sanitary Items',
            'slug'      => 'sanitary-items',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id'         => $category->id,
            'name'                => 'Kitchen Mixer Faucet',
            'slug'                => 'kitchen-mixer-faucet',
            'sku'                 => 'MIX-101',
            'unit'                => 'pc',
            'cost_price'          => 1200.00,
            'sale_price'          => 2200.00,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 2,
            'is_active'           => true,
            'show_in_catalog'     => true,
        ]);

        // 1. In-stock product is visible on home, category, and detail pages
        $homeRes = $this->get(route('catalog.home'));
        $homeRes->assertOk()->assertSee('Kitchen Mixer Faucet');

        $catRes = $this->get(route('catalog.category', $category->slug));
        $catRes->assertOk()->assertSee('Kitchen Mixer Faucet');

        $searchRes = $this->get(route('catalog.search', ['q' => 'Kitchen Mixer']));
        $searchRes->assertOk()->assertSee('Kitchen Mixer Faucet');

        $prodRes = $this->get(route('catalog.product', $product->slug));
        $prodRes->assertOk()->assertSee('Kitchen Mixer Faucet');

        // 2. Product goes Out of Stock (stock = 0)
        $product->update(['stock_quantity' => 0]);

        $homeResOut = $this->get(route('catalog.home'));
        $homeResOut->assertDontSee('Kitchen Mixer Faucet');

        $catResOut = $this->get(route('catalog.category', $category->slug));
        $catResOut->assertDontSee('Kitchen Mixer Faucet');

        $searchResOut = $this->get(route('catalog.search', ['q' => 'Kitchen Mixer']));
        $searchResOut->assertDontSee('Kitchen Mixer Faucet');

        $prodResOut = $this->get(route('catalog.product', $product->slug));
        $prodResOut->assertNotFound();

        // 3. Product gets Restocked (stock = 15)
        $product->update(['stock_quantity' => 15]);

        $homeResRestocked = $this->get(route('catalog.home'));
        $homeResRestocked->assertSee('Kitchen Mixer Faucet');

        $catResRestocked = $this->get(route('catalog.category', $category->slug));
        $catResRestocked->assertSee('Kitchen Mixer Faucet');

        $prodResRestocked = $this->get(route('catalog.product', $product->slug));
        $prodResRestocked->assertOk()->assertSee('Kitchen Mixer Faucet');
    }
}
