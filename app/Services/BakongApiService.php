<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BakongApiService
{
    public function checkTransactionByMD5(string $md5): array
    {
        if (trim($md5) === '') {
            throw new RuntimeException('Bakong MD5 cannot be blank.');
        }

        return $this->post('/v1/check_transaction_by_md5', [
            'md5' => $md5,
        ], true);
    }

    public function checkBakongAccount(string $accountId): array
    {
        if (trim($accountId) === '') {
            throw new RuntimeException('Bakong account ID cannot be blank.');
        }

        return $this->post('/v1/check_bakong_account', [
            'accountId' => $accountId,
        ], false);
    }

    private function post(string $path, array $payload, bool $withToken): array
    {
        $request = Http::acceptJson()
            ->asJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->retry(3, 750);

        if ($withToken) {
            $token = (string) config('services.bakong.token');
            if (trim($token) === '') {
                throw new RuntimeException('Bakong token is not configured.');
            }

            $request = $request->withToken($token);
        }

        try {
            $response = $request->post($this->baseUrl().$path, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'Unable to reach the Bakong API. Check outbound access to '.$this->baseUrl().'.',
                previous: $e
            );
        }

        return $this->decodeResponse($response);
    }

    private function baseUrl(): string
    {
        $baseUrl = (string) config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh');
        $baseUrl = trim($baseUrl);

        if ($baseUrl === '') {
            throw new RuntimeException('Bakong API URL is not configured.');
        }

        if (!preg_match('#^https?://#i', $baseUrl)) {
            $baseUrl = 'https://'.$baseUrl;
        }

        return rtrim($baseUrl, '/');
    }

    private function decodeResponse(Response $response): array
    {
        $data = $response->json();

        if (!is_array($data)) {
            if ($response->failed()) {
                $summary = trim(strip_tags($response->body()));
                $summary = preg_replace('/\s+/', ' ', $summary ?? '');
                $summary = $summary !== '' ? mb_substr($summary, 0, 160) : 'Bakong API request failed.';

                throw new RuntimeException(
                    'Bakong API returned HTTP '.$response->status().': '.$summary,
                    $response->status()
                );
            }

            throw new RuntimeException('Bakong API returned an invalid JSON response.');
        }

        if ($response->successful()) {
            return $data;
        }

        $message = $data['responseMessage']
            ?? $data['message']
            ?? $response->body()
            ?? 'Bakong API request failed.';

        throw new RuntimeException((string) $message, $response->status());
    }
}
