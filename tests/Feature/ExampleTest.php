<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_shows_total_bought_for_each_product(): void
    {
        $firstProduct = Product::create([
            'name' => 'Phone One',
            'description' => 'First product',
            'price' => 100,
        ]);

        $secondProduct = Product::create([
            'name' => 'Phone Two',
            'description' => 'Second product',
            'price' => 200,
        ]);

        Order::create([
            'product_id' => $firstProduct->id,
            'product_name' => 'Cart order',
            'amount' => 300,
            'currency' => 'USD',
            'md5' => 'home-page-paid-order-1',
            'bill_number' => 'ORD-TEST-001',
            'status' => 'PAID',
            'paid_at' => now(),
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
                    'price' => 200,
                    'qty' => 1,
                ],
            ],
        ]);

        Order::create([
            'product_id' => $firstProduct->id,
            'product_name' => $firstProduct->name,
            'amount' => 100,
            'currency' => 'USD',
            'md5' => 'home-page-paid-order-2',
            'bill_number' => 'ORD-TEST-002',
            'status' => 'PAID',
            'paid_at' => now(),
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
            'product_id' => $firstProduct->id,
            'product_name' => $firstProduct->name,
            'amount' => 500,
            'currency' => 'USD',
            'md5' => 'home-page-pending-order',
            'bill_number' => 'ORD-TEST-003',
            'status' => 'PENDING',
            'items' => [
                [
                    'id' => $firstProduct->id,
                    'name' => $firstProduct->name,
                    'price' => 100,
                    'qty' => 5,
                ],
            ],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Bought: 3');
        $response->assertSee('Bought: 1');
    }
}
