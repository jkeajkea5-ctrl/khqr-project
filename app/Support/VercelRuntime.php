<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Slide;
use Database\Seeders\VercelBootstrapSeeder;
use Database\Seeders\VercelDemoSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
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
        self::ensureDirectory('/tmp/cache/data');
        $compiledViewPath = self::compiledViewPath();
        self::ensureDirectory($compiledViewPath);

        self::setIfMissing('APP_PACKAGES_CACHE', '/tmp/bootstrap/cache/packages.php');
        self::setIfMissing('APP_SERVICES_CACHE', '/tmp/bootstrap/cache/services.php');
        self::set('VIEW_COMPILED_PATH', $compiledViewPath);
        self::setIfMissing('LOG_CHANNEL', 'stderr');
        self::setIfMissing('SESSION_DRIVER', 'cookie');
        self::setIfMissing('CACHE_STORE', 'file');
        self::setIfMissing('CACHE_PATH', '/tmp/cache/data');
        self::setIfMissing('CACHE_LOCK_PATH', '/tmp/cache/data');
        self::setIfMissing('QUEUE_CONNECTION', 'sync');
        self::setIfMissing('APP_DEBUG', 'false');

        if (!self::hasValue('APP_KEY')) {
            self::set('APP_KEY', self::fallbackAppKey());
        }

        if (!self::hasValue('DB_CONNECTION')) {
            if (self::hasValue('MONGODB_URI') || self::hasValue('DB_URI')) {
                self::set('DB_CONNECTION', 'mongodb');
            } elseif (!self::hasValue('DB_URL') && !self::hasValue('DB_HOST')) {
                self::set('DB_CONNECTION', 'sqlite');
            }
        }

        if (self::value('DB_CONNECTION') === 'sqlite' && !self::hasValue('DB_DATABASE') && !self::hasValue('DB_URL')) {
            self::set('DB_DATABASE', '/tmp/database.sqlite');
        }

        if (self::value('DB_CONNECTION') === 'mongodb' && !self::hasValue('MEDIA_DISK')) {
            self::set('MEDIA_DISK', 'gridfs');
        }
    }

    public static function prepareDatabase(Application $app): void
    {
        if (!self::isVercel()) {
            return;
        }

        $defaultConnection = (string) $app['config']->get('database.default');

        if ($defaultConnection !== 'sqlite') {
            if (self::hasPreparedMarker($defaultConnection)) {
                return;
            }

            self::ensureRealDatabaseSchema($defaultConnection);
            self::repairLegacyMongoCatalog($defaultConnection);
            self::ensureBootstrapData();
            self::ensureAdminAccount();
            self::ensureManagedMediaInDisk();
            self::writePreparedMarker($defaultConnection);

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

    private static function ensureRealDatabaseSchema(string $databaseDriver): void
    {
        if ($databaseDriver === 'mongodb') {
            return;
        }

        if (!self::shouldAutoMigrate() || self::hasCoreTables()) {
            return;
        }

        Artisan::call('migrate', [
            '--force' => true,
        ]);
    }

    private static function repairLegacyMongoCatalog(string $databaseDriver): void
    {
        if ($databaseDriver !== 'mongodb' || !self::hasLegacyMongoCatalog()) {
            return;
        }

        Product::query()->delete();
        Category::query()->delete();
        Slide::query()->delete();
    }

    private static function ensureBootstrapData(): void
    {
        if (self::hasCatalogData()) {
            return;
        }

        app(VercelBootstrapSeeder::class)->run();
    }

    private static function hasLegacyMongoCatalog(): bool
    {
        try {
            if (Order::query()->exists()) {
                return false;
            }

            return Product::query()
                ->whereIn('category_id', [1, 2, 3, '1', '2', '3'])
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private static function ensureAdminAccount(): void
    {
        try {
            $adminsExist = Admin::query()->exists();
        } catch (\Throwable) {
            $adminsExist = false;
        }

        $email = trim((string) self::value('ADMIN_EMAIL'));
        $name = trim((string) self::value('ADMIN_NAME'));
        $password = trim((string) self::value('ADMIN_PASSWORD'));
        $role = trim((string) self::value('ADMIN_ROLE'));

        if ($email === '') {
            return;
        }

        $admin = Admin::query()->firstOrNew([
            'email' => $email,
        ]);

        if (!$admin->exists && $password === '' && !$adminsExist) {
            return;
        }

        $admin->name = $name !== '' ? $name : ($admin->name ?: 'Admin');
        $admin->role = in_array($role, array_keys(Admin::roleOptions()), true)
            ? $role
            : ($admin->role ?: Admin::ROLE_ADMIN);

        if ($password !== '' && (!$admin->exists || self::boolValue('ADMIN_SYNC_PASSWORD'))) {
            $admin->password = $password;
        }

        $admin->save();
    }

    private static function ensureManagedMediaInDisk(): void
    {
        if (MediaStorage::disk() === 'public') {
            return;
        }

        $paths = [];

        foreach (Product::query()->select(['image', 'colors'])->cursor() as $product) {
            $paths[] = MediaPath::normalize($product->getRawOriginal('image') ?: $product->image);

            foreach (self::productColorImages($product) as $imagePath) {
                $paths[] = $imagePath;
            }
        }

        foreach (Slide::query()->select(['image'])->cursor() as $slide) {
            $paths[] = MediaPath::normalize($slide->getRawOriginal('image') ?: $slide->image);
        }

        foreach (array_unique(array_filter($paths)) as $path) {
            if (!is_string($path) || MediaStorage::exists($path)) {
                continue;
            }

            foreach (self::trackedMediaCandidates($path) as $candidate) {
                if (!is_file($candidate)) {
                    continue;
                }

                MediaStorage::putFromFile($path, $candidate);
                break;
            }
        }
    }

    private static function productColorImages(Product $product): array
    {
        $rawColors = $product->getRawOriginal('colors');
        $colors = is_string($rawColors) && $rawColors !== ''
            ? json_decode($rawColors, true)
            : (is_array($product->colors) ? $product->colors : []);

        $paths = [];
        foreach ((array) $colors as $color) {
            if (!is_array($color)) {
                continue;
            }

            $paths[] = MediaPath::normalize($color['image'] ?? null);
        }

        return $paths;
    }

    private static function trackedMediaCandidates(string $path): array
    {
        return [
            public_path('vercel-storage/'.$path),
            storage_path('app/public/'.$path),
        ];
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

    private static function hasCoreTables(): bool
    {
        try {
            return Schema::hasTable('users')
                && Schema::hasTable('admins')
                && Schema::hasTable('catagories')
                && Schema::hasTable('products')
                && Schema::hasTable('orders')
                && Schema::hasTable('slides');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function hasCatalogData(): bool
    {
        try {
            return Category::query()->exists()
                || Product::query()->exists()
                || Slide::query()->exists();
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

    private static function compiledViewPath(): string
    {
        $signature = self::value('VERCEL_GIT_COMMIT_SHA')
            ?: self::value('VERCEL_URL')
            ?: 'runtime';

        return '/tmp/views/'.sha1($signature);
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

    private static function shouldAutoMigrate(): bool
    {
        return self::boolValue('VERCEL_AUTO_MIGRATE', true);
    }

    private static function hasPreparedMarker(string $defaultConnection): bool
    {
        return is_file(self::preparedMarkerPath($defaultConnection));
    }

    private static function writePreparedMarker(string $defaultConnection): void
    {
        $path = self::preparedMarkerPath($defaultConnection);
        self::ensureDirectory(dirname($path));

        @file_put_contents($path, json_encode([
            'connection' => $defaultConnection,
            'media_disk' => MediaStorage::disk(),
            'database' => self::value('MONGODB_DATABASE') ?: self::value('DB_DATABASE') ?: null,
            'commit' => self::value('VERCEL_GIT_COMMIT_SHA') ?: null,
            'prepared_at' => gmdate(DATE_ATOM),
        ], JSON_UNESCAPED_SLASHES));
    }

    private static function preparedMarkerPath(string $defaultConnection): string
    {
        $signature = implode('|', [
            $defaultConnection,
            MediaStorage::disk(),
            self::value('MONGODB_DATABASE') ?: self::value('DB_DATABASE') ?: 'default',
            self::value('ADMIN_EMAIL') ?: '',
            self::value('VERCEL_GIT_COMMIT_SHA') ?: self::value('VERCEL_URL') ?: 'runtime',
        ]);

        return '/tmp/bootstrap/runtime-'.sha1($signature).'.json';
    }

    private static function boolValue(string $key, bool $default = false): bool
    {
        $value = self::value($key);

        if (!is_string($value)) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private static function value(string $key): string|false
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    }
}
