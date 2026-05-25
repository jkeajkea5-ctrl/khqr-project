<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_staff_management_and_users_page(): void
    {
        $admin = Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_ADMIN,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.admins.index'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
    }

    public function test_stock_controller_cannot_access_staff_management_or_delete_products(): void
    {
        $admin = Admin::create([
            'name' => 'Stock Team',
            'email' => 'stock@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_STOCK_CONTROLLER,
        ]);

        $product = Product::create([
            'name' => 'Phone',
            'description' => 'Demo product',
            'price' => 100,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.admins.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_employee_can_view_users_and_products_but_cannot_edit_them(): void
    {
        $admin = Admin::create([
            'name' => 'Shop Staff',
            'email' => 'employee@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_EMPLOYEE,
        ]);

        $product = Product::create([
            'name' => 'Phone',
            'description' => 'Demo product',
            'price' => 100,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.products.edit', $product))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_create_staff_with_picture(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_ADMIN,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post(route('admin.admins.store'), [
            'name' => 'Photo Staff',
            'email' => 'photo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => Admin::ROLE_EMPLOYEE,
            'photo' => UploadedFile::fake()->createWithContent(
                'staff-avatar.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a6XcAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect(route('admin.admins.index'));

        $created = Admin::where('email', 'photo@example.com')->firstOrFail();

        $this->assertNotNull($created->photoStoragePath());
        Storage::disk('public')->assertExists($created->photoStoragePath());
    }
}
