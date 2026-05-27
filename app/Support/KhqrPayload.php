<?php

namespace App\Support;

use KHQR\Helpers\EMV;
use KHQR\Helpers\Utils;

final class KhqrPayload
{
    public static function normalizeUsdAmount(string $qr, float $amount): string
    {
        $segments = self::segments($qr);
        if ($segments === []) {
            return $qr;
        }

        $normalizedAmount = number_format($amount, 2, '.', '');
        $payload = '';
        $hasUsdCurrency = false;
        $hasAmount = false;

        foreach ($segments as $segment) {
            if ($segment['tag'] === EMV::CRC) {
                continue;
            }

            if ($segment['tag'] === EMV::TRANSACTION_CURRENCY) {
                $hasUsdCurrency = $segment['value'] === '840';
            }

            if ($segment['tag'] === EMV::TRANSACTION_AMOUNT) {
                $payload .= self::tlv($segment['tag'], $normalizedAmount);
                $hasAmount = true;

                continue;
            }

            $payload .= $segment['raw'];
        }

        if (!$hasUsdCurrency || !$hasAmount) {
            return $qr;
        }

        $crcBase = $payload.EMV::CRC.EMV::CRC_LENGTH;

        return $crcBase.Utils::crc16($crcBase);
    }

    public static function ensureDynamicExpiry(string $qr, int $expirySeconds): string
    {
        if ($expirySeconds < 1) {
            return $qr;
        }

        $segments = self::segments($qr);
        if ($segments === []) {
            return $qr;
        }

        $payload = '';
        $creationTimestamp = null;
        $isDynamic = false;

        foreach ($segments as $segment) {
            if ($segment['tag'] === EMV::POINT_OF_INITIATION_METHOD) {
                $isDynamic = $segment['value'] === EMV::DYNAMIC_QR;
            }

            if ($segment['tag'] === EMV::TIMESTAMP_TAG || $segment['tag'] === EMV::CRC) {
                if ($segment['tag'] === EMV::TIMESTAMP_TAG) {
                    $creationTimestamp = self::extractCreationTimestamp($segment['value']);
                }

                continue;
            }

            $payload .= $segment['raw'];
        }

        if (!$isDynamic) {
            return $qr;
        }

        $creationTimestamp ??= (int) floor(microtime(true) * 1000);
        $expiryTimestamp = $creationTimestamp + ($expirySeconds * 1000);
        $timestampValue = self::tlv('00', (string) $creationTimestamp)
            .self::tlv('01', (string) $expiryTimestamp);

        $payload .= self::tlv(EMV::TIMESTAMP_TAG, $timestampValue);

        $crcBase = $payload.EMV::CRC.EMV::CRC_LENGTH;

        return $crcBase.Utils::crc16($crcBase);
    }

    private static function extractCreationTimestamp(string $value): ?int
    {
        if (preg_match('/^0013(\d{13})0113\d{13}$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/^0013(\d{13})$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/^\d{13}$/', $value) === 1) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @return array<int, array{tag: string, value: string, raw: string}>
     */
    private static function segments(string $qr): array
    {
        $segments = [];
        $offset = 0;
        $length = strlen($qr);

        while ($offset + 4 <= $length) {
            $tag = substr($qr, $offset, 2);
            $valueLength = (int) substr($qr, $offset + 2, 2);
            $segmentLength = 4 + $valueLength;

            if ($offset + $segmentLength > $length) {
                return [];
            }

            $raw = substr($qr, $offset, $segmentLength);
            $segments[] = [
                'tag' => $tag,
                'value' => substr($qr, $offset + 4, $valueLength),
                'raw' => $raw,
            ];

            $offset += $segmentLength;
        }

        return $offset === $length ? $segments : [];
    }

    private static function tlv(string $tag, string $value): string
    {
        return $tag.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }
}
