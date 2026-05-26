<?php

namespace App\Services;

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
