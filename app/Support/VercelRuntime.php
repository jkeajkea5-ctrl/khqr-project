<?php

namespace App\Support;

use Database\Seeders\VercelDemoSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        self::resetDatabase($databasePath);
        self::createDemoTables();

        app(VercelDemoSeeder::class)->run();
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

    private static function resetDatabase(string $databasePath): void
    {
        DB::purge('sqlite');

        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        touch($databasePath);
        DB::reconnect('sqlite');
    }

    private static function createDemoTables(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->string('photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('catagories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->text('sizes')->nullable();
            $table->text('colors')->nullable();
            $table->timestamps();
        });

        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->text('items')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('md5')->nullable();
            $table->string('bill_number')->nullable();
            $table->string('status')->default('PENDING');
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
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
