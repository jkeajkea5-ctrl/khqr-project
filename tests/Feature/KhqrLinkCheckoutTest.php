<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KhqrLinkCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_includes_the_configured_khqr_link_api_key(): void
    {
        Http::fake([
            'https://api.khqr.link/v1/khqr/create*' => Http::response([
                'status' => 'success',
                'qr' => 'http://api.khqr.link/v1/qr/TEST12345',
                'md5' => 'checkout-md5',
                'tran' => 'TEST12345',
                'amount' => 10.50,
                'currency' => 'USD',
                'merchantname' => 'Finch',
                'created_at' => now()->toIso8601String(),
                'expires_at' => now()->addMinutes(3)->toIso8601String(),
            ], 200),
        ]);

        config([
            'services.bakong.payment_provider' => 'khqr_link',
            'services.bakong.khqr_link_api_url' => 'https://api.khqr.link',
            'services.bakong.khqr_link_api_key' => 'khqr_test_key',
            'services.bakong.account_id' => 'seavfong_mam@bkrt',
            'services.bakong.merchant_name' => 'Finch',
            'services.bakong.currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $product = Product::create([
            'name' => 'Team 10 Shirt',
            'price' => 10.50,
        ]);

        $response = $this->actingAs($user)->post(route('checkout', $product->id));

        $response->assertOk()
            ->assertViewHas('md5', 'checkout-md5')
            ->assertViewHas('qrImageUrl', 'https://api.khqr.link/v1/qr/TEST12345')
            ->assertDontSeeText('Paid already but not confirmed?')
            ->assertSee('const autoConfirmStartsImmediately = true;', false);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://api.khqr.link/v1/khqr/create')
                && $request['amount'] === '10.50'
                && $request['bakongid'] === 'seavfong_mam@bkrt'
                && $request['merchantname'] === 'Finch'
                && $request['apikey'] === 'khqr_test_key'
                && $request->hasHeader('X-API-Key', 'khqr_test_key');
        });
    }

    public function test_verify_transaction_confirms_the_order_by_md5_without_an_order_id(): void
    {
        Http::fake([
            'https://api.khqr.link/v1/khqr/check*' => Http::response([
                'responseCode' => 0,
                'responseMessage' => 'Success',
                'status' => 'SUCCESS',
                'verified' => true,
                'md5' => 'verify-md5',
            ], 200),
        ]);

        config([
            'services.bakong.payment_provider' => 'khqr_link',
            'services.bakong.khqr_link_api_url' => 'https://api.khqr.link',
            'services.bakong.khqr_link_api_key' => 'khqr_test_key',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $product = Product::create([
            'name' => 'Team 10 Shirt',
            'price' => 10.50,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'amount' => 10.50,
            'currency' => 'USD',
            'md5' => 'verify-md5',
            'bill_number' => 'ORD-VERIFY-001',
            'status' => 'PENDING',
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'qty' => 1,
                    'price' => 10.50,
                ],
            ],
        ]);

        $response = $this->postJson(route('verify.transaction'), [
            'md5' => 'verify-md5',
        ]);

        $response->assertOk()
            ->assertJson([
                'responseCode' => 0,
                'order_id' => $order->id,
            ])
            ->assertJsonStructure([
                'invoice_url',
            ]);

        $invoiceUrl = (string) $response->json('invoice_url');
        $this->assertStringContainsString('/invoice/'.$order->id, $invoiceUrl);

        $order->refresh();

        $this->assertSame('PAID', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_signed_invoice_url_from_verify_can_be_opened_without_login(): void
    {
        Http::fake([
            'https://api.khqr.link/v1/khqr/check*' => Http::response([
                'responseCode' => 0,
                'responseMessage' => 'Success',
                'status' => 'COMPLETED',
                'verified' => true,
                'md5' => 'signed-invoice-md5',
            ], 200),
        ]);

        config([
            'services.bakong.payment_provider' => 'khqr_link',
            'services.bakong.khqr_link_api_url' => 'https://api.khqr.link',
            'services.bakong.khqr_link_api_key' => 'khqr_test_key',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $product = Product::create([
            'name' => 'Team 10 Shirt',
            'price' => 10.50,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'amount' => 10.50,
            'currency' => 'USD',
            'md5' => 'signed-invoice-md5',
            'bill_number' => 'ORD-VERIFY-002',
            'status' => 'PENDING',
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'qty' => 1,
                    'price' => 10.50,
                ],
            ],
        ]);

        $verifyResponse = $this->postJson(route('verify.transaction'), [
            'md5' => 'signed-invoice-md5',
        ]);

        $verifyResponse->assertOk();

        $this->get($verifyResponse->json('invoice_url'))
            ->assertOk()
            ->assertSee('Order #'.$order->id);
    }
}
