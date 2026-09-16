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

        $category = Category::firstOrCreate(
            ['slug' => 'sanitary'],
            ['name' => 'Sanitary', 'is_active' => true]
        );

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

    public function test_admin_can_create_update_and_manage_administrators(): void
    {
        // 1. Create new admin
        $response = $this->actingAs($this->admin)
            ->post(route('admin.staff.admins.store'), [
                'name'     => 'New Admin User',
                'email'    => 'newadmin@hassanandsonscorp.com',
                'password' => 'SecretAdmin123',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'  => 'New Admin User',
            'email' => 'newadmin@hassanandsonscorp.com',
            'role'  => 'admin',
        ]);

        $newAdmin = User::where('email', 'newadmin@hassanandsonscorp.com')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('SecretAdmin123', $newAdmin->password));

        // 2. Update admin credentials
        $responseUpdate = $this->actingAs($this->admin)
            ->put(route('admin.staff.admins.update', $newAdmin), [
                'name'      => 'Updated Admin Name',
                'email'     => 'updatedadmin@hassanandsonscorp.com',
                'password'  => 'UpdatedSecret456',
                'is_active' => 1,
            ]);

        $responseUpdate->assertRedirect()
            ->assertSessionHas('success');

        $newAdmin->refresh();
        $this->assertEquals('Updated Admin Name', $newAdmin->name);
        $this->assertEquals('updatedadmin@hassanandsonscorp.com', $newAdmin->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('UpdatedSecret456', $newAdmin->password));

        // 3. Prevent self-deletion
        $responseSelfDelete = $this->actingAs($this->admin)
            ->delete(route('admin.staff.admins.destroy', $this->admin));

        $responseSelfDelete->assertStatus(400);

        // 4. Delete the other admin
        $responseDelete = $this->actingAs($this->admin)
            ->delete(route('admin.staff.admins.destroy', $newAdmin));

        $responseDelete->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $newAdmin->id]);

        // 5. Deleting last remaining admin is blocked
        $responseLastDelete = $this->actingAs($this->admin)
            ->delete(route('admin.staff.admins.destroy', $this->admin));
        $responseLastDelete->assertStatus(400);
    }

    public function test_admin_can_search_staff_across_customers_cashiers_and_admins(): void
    {
        $customer = \App\Models\Employee::create([
            'name'      => 'Tariq Mehmood',
            'phone'     => '03335557788',
            'address'   => 'Rawalpindi Satellite Town',
            'is_active' => true,
            'notes'     => 'Master Plumber Contractor',
        ]);

        $cashier2 = User::factory()->create([
            'name'      => 'Zeeshan Cashier',
            'email'     => 'zeeshan@posstore.com',
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        // 1. Search for customer by name
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.index', ['search' => 'Tariq']));

        $response->assertOk()
            ->assertSee('Tariq Mehmood')
            ->assertSee('03335557788')
            ->assertDontSee('Zeeshan Cashier');

        // 2. Search for cashier by email
        $responseCashier = $this->actingAs($this->admin)
            ->get(route('admin.staff.index', ['search' => 'zeeshan@posstore.com']));

        $responseCashier->assertOk()
            ->assertSee('Zeeshan Cashier')
            ->assertDontSee('Tariq Mehmood');

        // 3. Search for admin by name
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('admin.staff.index', ['search' => $this->admin->name]));

        $responseAdmin->assertOk()
            ->assertSee($this->admin->name)
            ->assertDontSee('Zeeshan Cashier');
    }
}

