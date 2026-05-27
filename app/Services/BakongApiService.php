<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use KHQR\BakongKHQR;
use RuntimeException;
use Throwable;

class BakongApiService
{
    public function checkTransactionByMD5(string $md5): array
    {
        if (trim($md5) === '') {
            throw new RuntimeException('Bakong MD5 cannot be blank.');
        }

        try {
            if ($this->usesKhqrLinkProvider()) {
                return $this->checkTransactionByKhqrLink($md5);
            }

            if ($this->shouldUseVerifyProxy()) {
                return $this->checkTransactionByProxy($md5);
            }

            if ($this->isVerifyProxyRequired()) {
                throw new RuntimeException(
                    'Bakong verify proxy is required on this deployment because Bakong only allows Cambodia IPs. '
                    .'Set BAKONG_VERIFY_URL to a Cambodia-hosted endpoint.'
                );
            }

            return (new BakongKHQR($this->token()))->checkTransactionByMD5($md5, $this->isSitEnvironment());
        } catch (Throwable $e) {
            throw new RuntimeException($this->normalizeMessage($e), (int) $e->getCode(), previous: $e);
        }
    }

    public function checkBakongAccount(string $accountId): array
    {
        if (trim($accountId) === '') {
            throw new RuntimeException('Bakong account ID cannot be blank.');
        }

        try {
            $response = BakongKHQR::checkBakongAccount($accountId, $this->isSitEnvironment());
            $data = is_array($response->data) ? $response->data : (array) $response->data;

            return [
                'responseCode' => (int) (($response->status['code'] ?? 1) === 0 ? 0 : 1),
                'responseMessage' => $response->status['message'] ?? null,
            ] + $data;
        } catch (Throwable $e) {
            throw new RuntimeException($this->normalizeMessage($e), (int) $e->getCode(), previous: $e);
        }
    }

    private function token(): string
    {
        $token = trim((string) config('services.bakong.token'));

        if ($token === '') {
            throw new RuntimeException('Bakong token is not configured.');
        }

        return $token;
    }

    private function paymentProvider(): string
    {
        $provider = strtolower(trim((string) config('services.bakong.payment_provider', 'bakong')));

        return $provider !== '' ? $provider : 'bakong';
    }

    private function usesKhqrLinkProvider(): bool
    {
        return $this->paymentProvider() === 'khqr_link';
    }

    private function shouldUseVerifyProxy(): bool
    {
        return $this->verifyUrl() !== '';
    }

    private function isVerifyProxyRequired(): bool
    {
        $required = config('services.bakong.verify_required', false);

        if (is_bool($required)) {
            return $required;
        }

        return in_array(strtolower(trim((string) $required)), ['1', 'true', 'yes', 'on'], true);
    }

    private function verifyUrl(): string
    {
        $url = trim((string) config('services.bakong.verify_url'));

        if ($url === '') {
            return '';
        }

        if (str_ends_with($url, '.php')) {
            return $url;
        }

        return rtrim($url, '/').'/';
    }

    private function verifySecret(): string
    {
        return trim((string) config('services.bakong.verify_secret'));
    }

    private function khqrLinkApiUrl(): string
    {
        $url = trim((string) config('services.bakong.khqr_link_api_url', 'https://api.khqr.link'));

        if ($url === '') {
            throw new RuntimeException('KHQR Link API URL is not configured.');
        }

        return rtrim($url, '/');
    }

    private function khqrLinkApiKey(): string
    {
        return trim((string) config('services.bakong.khqr_link_api_key'));
    }

    private function khqrLinkRequest(): PendingRequest
    {
        $request = Http::acceptJson()
            ->timeout(20)
            ->connectTimeout(10);

        $apiKey = $this->khqrLinkApiKey();
        if ($apiKey !== '') {
            $request = $request->withHeaders([
                'X-API-Key' => $apiKey,
            ]);
        }

        return $request;
    }

    private function checkTransactionByKhqrLink(string $md5): array
    {
        try {
            $response = $this->khqrLinkRequest()
                ->get($this->khqrLinkApiUrl().'/v1/khqr/check', [
                    'md5' => $md5,
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to reach the KHQR Link API.', (int) $e->getCode(), $e);
        }

        $payload = $response->json();

        if (is_array($payload) && (
            array_key_exists('responseCode', $payload)
            || array_key_exists('status', $payload)
            || array_key_exists('verified', $payload)
        )) {
            return $payload;
        }

        if (is_array($payload)) {
            $message = trim((string) ($payload['message'] ?? $payload['error'] ?? 'KHQR Link verification failed.'));

            throw new RuntimeException($message !== '' ? $message : 'KHQR Link verification failed.', $response->status());
        }

        throw new RuntimeException('KHQR Link API returned an unexpected response.', $response->status());
    }

    private function checkTransactionByProxy(string $md5): array
    {
        $request = Http::asJson()
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(10);

        $secret = $this->verifySecret();
        if ($secret !== '') {
            $request = $request->withHeaders([
                'X-Bakong-Verify-Secret' => $secret,
            ]);
        }

        try {
            $response = $request->post($this->verifyUrl(), [
                'md5' => $md5,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to reach the Bakong verify proxy.', (int) $e->getCode(), $e);
        }

        $payload = $response->json();
        if (is_array($payload) && $response->successful()) {
            $error = trim((string) ($payload['error'] ?? ''));
            if ($error !== '') {
                throw new RuntimeException('Bakong verify proxy request failed: '.$error, $response->status());
            }

            return $payload;
        }

        if (is_array($payload)) {
            $message = trim((string) ($payload['error'] ?? $payload['responseMessage'] ?? 'Bakong verify proxy request failed.'));

            throw new RuntimeException($message !== '' ? $message : 'Bakong verify proxy request failed.', $response->status());
        }

        throw new RuntimeException('Bakong verify proxy returned an unexpected response.', $response->status());
    }

    private function isSitEnvironment(): bool
    {
        $baseUrl = trim((string) config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh'));

        return str_contains(strtolower($baseUrl), 'sit-api-bakong');
    }

    private function normalizeMessage(Throwable $e): string
    {
        $message = trim(strip_tags((string) $e->getMessage()));
        $message = preg_replace('/\s+/', ' ', $message ?? '');

        if ($message === '') {
            return 'Bakong API request failed.';
        }

        if (preg_match('/^(\d{3})\s+(.+)$/', $message, $matches) === 1) {
            return 'Bakong API returned HTTP '.$matches[1].': '.mb_substr(trim($matches[2]), 0, 160);
        }

        return mb_substr($message, 0, 200);
    }
}
