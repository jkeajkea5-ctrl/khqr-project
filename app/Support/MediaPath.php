<?php

namespace App\Support;

use Illuminate\Support\Str;

class MediaPath
{
    private const PUBLIC_DISK_DIRECTORIES = [
        'admins/',
        'products/',
        'product-colors/',
        'slides/',
    ];

    public static function normalize(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['data:', 'blob:'])) {
            return $value;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            $path = (string) parse_url($value, PHP_URL_PATH);
            $host = (string) parse_url($value, PHP_URL_HOST);

            if ($path !== '' && str_contains($path, '/storage/') && self::isOwnHost($host)) {
                return ltrim(Str::after($path, '/storage/'), '/');
            }

            return $value;
        }

        if (str_contains($value, '/storage/')) {
            return ltrim(Str::after($value, '/storage/'), '/');
        }

        if (Str::startsWith($value, 'storage/')) {
            return ltrim(Str::after($value, 'storage/'), '/');
        }

        return ltrim($value, '/');
    }

    public static function resolve(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['data:', 'blob:'])) {
            return $value;
        }

        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        if (Str::startsWith($normalized, ['http://', 'https://', '//'])) {
            return $normalized;
        }

        if (self::isPublicDiskPath($normalized)) {
            return asset('storage/'.$normalized);
        }

        return asset($normalized);
    }

    private static function isPublicDiskPath(string $path): bool
    {
        return Str::startsWith($path, self::PUBLIC_DISK_DIRECTORIES);
    }

    private static function isOwnHost(string $host): bool
    {
        $host = strtolower(trim($host));

        if ($host === '') {
            return false;
        }

        $knownHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
        ];

        $appUrlHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (is_string($appUrlHost) && $appUrlHost !== '') {
            $knownHosts[] = strtolower($appUrlHost);
        }

        if (app()->bound('request')) {
            $requestHost = app('request')->getHost();
            if (is_string($requestHost) && $requestHost !== '') {
                $knownHosts[] = strtolower($requestHost);
            }
        }

        return in_array($host, array_unique($knownHosts), true);
    }
}
