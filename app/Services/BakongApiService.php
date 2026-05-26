<?php

namespace App\Services;

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
            if ($this->verifyUrl() !== '') {
                return $this->checkTransactionViaProxy($md5);
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

    private function verifyUrl(): string
    {
        return trim((string) config('services.bakong.verify_url', ''));
    }

    private function verifySecret(): string
    {
        return trim((string) config('services.bakong.verify_secret', ''));
    }

    private function isSitEnvironment(): bool
    {
        $baseUrl = trim((string) config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh'));

        return str_contains(strtolower($baseUrl), 'sit-api-bakong');
    }

    private function checkTransactionViaProxy(string $md5): array
    {
        $request = Http::acceptJson()
            ->asJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->retry(2, 500);

        if ($this->verifySecret() !== '') {
            $request = $request->withHeader('X-Bakong-Verify-Secret', $this->verifySecret());
        }

        $response = $request->post($this->verifyUrl(), [
            'md5' => $md5,
        ]);

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Bakong verify proxy returned an invalid JSON response.');
        }

        if ($response->successful()) {
            return $data;
        }

        $message = $data['error']
            ?? $data['responseMessage']
            ?? $response->body()
            ?? 'Bakong verify proxy request failed.';

        throw new RuntimeException((string) $message, $response->status());
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
