<?php

namespace App\Support;

use Database\Seeders\VercelDemoSeeder;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;

class VercelRuntime
{
    public static function configureEnvironment(): void
    {
        if (!self::isVercel()) {
            return;
        }

        self::ensureDirectory('/tmp/bootstrap/cache');
        self::ensureDirectory('/tmp/views');

        self::setIfMissing('APP_PACKAGES_CACHE', '/tmp/bootstrap/cache/packages.php');
        self::setIfMissing('APP_SERVICES_CACHE', '/tmp/bootstrap/cache/services.php');
        self::setIfMissing('VIEW_COMPILED_PATH', '/tmp/views');
        self::setIfMissing('LOG_CHANNEL', 'stderr');
        self::setIfMissing('SESSION_DRIVER', 'cookie');
        self::setIfMissing('CACHE_STORE', 'array');
        self::setIfMissing('QUEUE_CONNECTION', 'sync');
        self::setIfMissing('APP_DEBUG', 'false');

        if (!self::hasValue('APP_KEY')) {
            self::set('APP_KEY', self::fallbackAppKey());
        }

        if (!self::hasValue('DB_CONNECTION') && !self::hasValue('DB_URL') && !self::hasValue('DB_HOST')) {
            self::set('DB_CONNECTION', 'sqlite');
        }

        if (self::value('DB_CONNECTION') === 'sqlite' && !self::hasValue('DB_DATABASE') && !self::hasValue('DB_URL')) {
            self::set('DB_DATABASE', '/tmp/database.sqlite');
        }
    }

    public static function prepareDatabase(Application $app): void
    {
        if (!self::isVercel() || $app['config']->get('database.default') !== 'sqlite') {
            return;
        }

        $databasePath = $app['config']->get('database.connections.sqlite.database');

        if (!is_string($databasePath) || $databasePath === '' || !str_starts_with($databasePath, '/tmp/')) {
            return;
        }

        self::ensureDirectory(dirname($databasePath));

        if (!file_exists($databasePath)) {
            touch($databasePath);
        }

        $needsProvisioning = !self::hasCatalogTables();

        if (!$needsProvisioning) {
            return;
        }

        $artisan = $app->make(ConsoleKernel::class);
        $artisan->call('migrate', ['--force' => true]);
        $artisan->call('db:seed', [
            '--class' => VercelDemoSeeder::class,
            '--force' => true,
        ]);
    }

    private static function hasCatalogTables(): bool
    {
        try {
            return Schema::hasTable('catagories')
                && Schema::hasTable('products')
                && Schema::hasTable('slides');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function fallbackAppKey(): string
    {
        $seed = self::value('VERCEL_GIT_COMMIT_SHA')
            ?: self::value('VERCEL_URL')
            ?: 'khqr-project-vercel-fallback';

        return 'base64:'.base64_encode(hash('sha256', $seed, true));
    }

    private static function isVercel(): bool
    {
        return self::hasValue('VERCEL');
    }

    private static function ensureDirectory(string $path): void
    {
        if (is_dir($path)) {
            return;
        }

        mkdir($path, 0777, true);
    }

    private static function setIfMissing(string $key, string $value): void
    {
        if (self::hasValue($key)) {
            return;
        }

        self::set($key, $value);
    }

    private static function set(string $key, string $value): void
    {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private static function hasValue(string $key): bool
    {
        $value = self::value($key);

        return is_string($value) && trim($value) !== '';
    }

    private static function value(string $key): string|false
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    }
}
