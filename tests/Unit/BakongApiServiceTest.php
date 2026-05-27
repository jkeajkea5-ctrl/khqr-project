<?php

namespace Tests\Unit;

use App\Services\BakongApiService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BakongApiServiceTest extends TestCase
{
    public function test_it_requires_a_verify_proxy_when_the_deployment_is_configured_to_use_one(): void
    {
        Config::set('services.bakong.payment_provider', 'bakong');
        Config::set('services.bakong.verify_required', true);
        Config::set('services.bakong.verify_url', '');

        $service = app(BakongApiService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bakong verify proxy is required on this deployment');

        $service->checkTransactionByMD5('abc123');
    }

    public function test_it_uses_the_configured_verify_proxy(): void
    {
        Http::fake([
            'https://proxy.example.com/bakong-verify/' => Http::response([
                'responseCode' => 0,
                'responseMessage' => 'Success',
            ], 200),
        ]);

        Config::set('services.bakong.payment_provider', 'bakong');
        Config::set('services.bakong.verify_required', true);
        Config::set('services.bakong.verify_url', 'https://proxy.example.com/bakong-verify');
        Config::set('services.bakong.verify_secret', 'shared-secret');

        $service = app(BakongApiService::class);
        $response = $service->checkTransactionByMD5('abc123');

        $this->assertSame(0, $response['responseCode']);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://proxy.example.com/bakong-verify/'
                && $request['md5'] === 'abc123'
                && $request->hasHeader('X-Bakong-Verify-Secret', 'shared-secret');
        });
    }

    public function test_it_treats_proxy_error_payloads_as_failures(): void
    {
        Http::fake([
            'https://proxy.example.com/bakong-verify/' => Http::response([
                'error' => '403 ERROR Request blocked by CloudFront',
                'responseCode' => 1,
            ], 200),
        ]);

        Config::set('services.bakong.payment_provider', 'bakong');
        Config::set('services.bakong.verify_url', 'https://proxy.example.com/bakong-verify');

        $service = app(BakongApiService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bakong verify proxy request failed');

        $service->checkTransactionByMD5('abc123');
    }

    public function test_it_checks_transactions_via_khqr_link_when_configured(): void
    {
        Http::fake([
            'https://api.khqr.link/v1/khqr/check*' => Http::response([
                'responseCode' => 1,
                'responseMessage' => 'Pending',
                'status' => 'PENDING',
                'verified' => false,
                'md5' => 'abc123',
            ], 200),
        ]);

        Config::set('services.bakong.payment_provider', 'khqr_link');
        Config::set('services.bakong.khqr_link_api_url', 'https://api.khqr.link');
        Config::set('services.bakong.khqr_link_api_key', 'khqr_test_key');

        $service = app(BakongApiService::class);
        $response = $service->checkTransactionByMD5('abc123');

        $this->assertSame(1, $response['responseCode']);
        $this->assertSame('PENDING', $response['status']);
        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://api.khqr.link/v1/khqr/check')
                && $request['md5'] === 'abc123'
                && $request->hasHeader('X-API-Key', 'khqr_test_key');
        });
    }

    public function test_it_surfaces_khqr_link_connection_failures(): void
    {
        Http::fake(function () {
            throw new \RuntimeException('connection refused');
        });

        Config::set('services.bakong.payment_provider', 'khqr_link');
        Config::set('services.bakong.khqr_link_api_url', 'https://api.khqr.link');

        $service = app(BakongApiService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to reach the KHQR Link API.');

        $service->checkTransactionByMD5('abc123');
    }
}
