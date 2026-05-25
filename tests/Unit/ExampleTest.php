<?php

namespace Tests\Unit;

use App\Models\Order;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    public function test_order_summarize_items_returns_real_product_names(): void
    {
        $summary = Order::summarizeItems([
            ['name' => 'iPhone 17 Pro'],
            ['name' => 'AirPods Pro'],
            ['name' => 'iPhone 17 Pro'],
        ], 'Cart order');

        $this->assertSame('iPhone 17 Pro, AirPods Pro', $summary);
    }

    public function test_order_display_product_name_uses_items_over_cart_order(): void
    {
        $order = new Order([
            'product_name' => 'Cart order',
            'items' => [
                ['name' => 'New Brand T-Shirt Polo'],
            ],
        ]);

        $this->assertSame('New Brand T-Shirt Polo', $order->display_product_name);
    }
}
