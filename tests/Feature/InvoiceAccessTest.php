<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InvoiceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_owner_can_view_paid_invoice(): void
    {
        $user = User::create([
            'name' => 'Shop User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $product = Product::create([
            'name' => 'T-Shirt',
            'description' => 'Demo product',
            'price' => 20,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => 'T-Shirt',
            'amount' => 20,
            'currency' => 'USD',
            'md5' => 'invoice-owner-md5',
            'bill_number' => 'INV-OWNER-001',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('invoice', $order))
            ->assertOk();
    }

    public function test_signed_invoice_link_can_be_opened_without_login(): void
    {
        $user = User::create([
            'name' => 'Shop User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $product = Product::create([
            'name' => 'T-Shirt',
            'description' => 'Demo product',
            'price' => 20,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => 'T-Shirt',
            'amount' => 20,
            'currency' => 'USD',
            'md5' => 'invoice-signed-md5',
            'bill_number' => 'INV-SIGNED-001',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        $signedUrl = URL::temporarySignedRoute('invoice', now()->addMinutes(30), [
            'order' => $order,
        ]);

        $this->get($signedUrl)->assertOk();
    }

    public function test_unsigned_guest_cannot_view_paid_invoice(): void
    {
        $user = User::create([
            'name' => 'Shop User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $product = Product::create([
            'name' => 'T-Shirt',
            'description' => 'Demo product',
            'price' => 20,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => 'T-Shirt',
            'amount' => 20,
            'currency' => 'USD',
            'md5' => 'invoice-guest-md5',
            'bill_number' => 'INV-GUEST-001',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        $this->get(route('invoice', $order))->assertForbidden();
    }

    public function test_admin_can_view_paid_invoice_without_signed_link(): void
    {
        $admin = Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_ADMIN,
        ]);

        $product = Product::create([
            'name' => 'T-Shirt',
            'description' => 'Demo product',
            'price' => 20,
        ]);

        $order = Order::create([
            'product_id' => $product->id,
            'product_name' => 'T-Shirt',
            'amount' => 20,
            'currency' => 'USD',
            'md5' => 'invoice-admin-md5',
            'bill_number' => 'INV-ADMIN-001',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('invoice', $order))
            ->assertOk();
    }
}
