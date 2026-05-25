<?php

use App\Support\VercelRuntime;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

VercelRuntime::configureEnvironment();

/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

try {
    $kernel = $app->make(HttpKernel::class);
    $kernel->bootstrap();

    VercelRuntime::prepareDatabase($app);

    if (isset($_GET['__diag'])) {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'app_env' => config('app.env'),
            'app_key_present' => filled(config('app.key')),
            'db_default' => config('database.default'),
            'sqlite_database' => config('database.connections.sqlite.database'),
            'categories' => \App\Models\Category::count(),
            'products' => \App\Models\Product::count(),
            'slides' => \App\Models\Slide::count(),
            'sample_image_url' => asset('storage/products/JuYMbyoeLg9o3zA5XZmCS3EYUobxxMcGU5Q8HL2x.jpg'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return;
    }

    $request = Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    http_response_code(500);

    if (!isset($_GET['__diag'])) {
        return;
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile().':'.$e->getLine(),
        'trace' => $e->getTraceAsString(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
