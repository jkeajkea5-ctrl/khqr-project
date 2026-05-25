<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_sales_analytics_from_paid_orders(): void
    {
        Carbon::setTestNow('2026-05-19 10:30:00');

        try {
            $admin = Admin::create([
                'name' => 'Main Admin',
                'email' => 'admin@example.com',
                'password' => 'password123',
                'role' => Admin::ROLE_ADMIN,
            ]);

            $firstProduct = Product::create([
                'name' => 'Phone One',
                'description' => 'First dashboard product',
                'price' => 100,
            ]);

            $secondProduct = Product::create([
                'name' => 'Phone Two',
                'description' => 'Second dashboard product',
                'price' => 100,
            ]);

            Order::create([
                'product_id' => $firstProduct->id,
                'product_name' => 'Cart order',
                'amount' => 300,
                'currency' => 'USD',
                'md5' => 'dashboard-paid-order-1',
                'bill_number' => 'ORD-DASH-001',
                'status' => 'PAID',
                'paid_at' => now()->subDay()->setTime(14, 0),
                'items' => [
                    [
                        'id' => $firstProduct->id,
                        'name' => $firstProduct->name,
                        'price' => 100,
                        'qty' => 2,
                    ],
                    [
                        'id' => $secondProduct->id,
                        'name' => $secondProduct->name,
                        'price' => 100,
                        'qty' => 1,
                    ],
                ],
            ]);

            Order::create([
                'product_id' => $firstProduct->id,
                'product_name' => $firstProduct->name,
                'amount' => 100,
                'currency' => 'USD',
                'md5' => 'dashboard-paid-order-2',
                'bill_number' => 'ORD-DASH-002',
                'status' => 'PAID',
                'paid_at' => now()->setTime(9, 15),
                'items' => [
                    [
                        'id' => $firstProduct->id,
                        'name' => $firstProduct->name,
                        'price' => 100,
                        'qty' => 1,
                    ],
                ],
            ]);

            Order::create([
                'product_id' => $secondProduct->id,
                'product_name' => $secondProduct->name,
                'amount' => 250,
                'currency' => 'USD',
                'md5' => 'dashboard-pending-order',
                'bill_number' => 'ORD-DASH-003',
                'status' => 'PENDING',
                'items' => [
                    [
                        'id' => $secondProduct->id,
                        'name' => $secondProduct->name,
                        'price' => 250,
                        'qty' => 1,
                    ],
                ],
            ]);

            $this->actingAs($admin, 'admin');

            $response = $this->get(route('admin.dashboard'));

            $response->assertOk();
            $response->assertSeeText('Revenue collected');
            $response->assertSeeText('$400.00');
            $response->assertSeeText('Units sold');
            $response->assertSeeText('Phone One');
            $response->assertSeeText('3 sold');
            $response->assertSeeText('Payment status');
        } finally {
            Carbon::setTestNow();
        }
    }
}
