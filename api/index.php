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

$kernel = $app->make(HttpKernel::class);
$kernel->bootstrap();

VercelRuntime::prepareDatabase($app);

$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
