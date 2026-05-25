<?php

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

if (isset($_GET['__diag'])) {
    header('Content-Type: application/json; charset=utf-8');

    $servicesCachePath = $app->getCachedServicesPath();

    echo json_encode([
        'laravel_version' => Application::VERSION,
        'config_cached' => $app->configurationIsCached(),
        'config_cache_path' => $app->getCachedConfigPath(),
        'config_cache_exists' => file_exists($app->getCachedConfigPath()),
        'services_cache_path' => $servicesCachePath,
        'services_cache_exists' => file_exists($servicesCachePath),
        'providers_file_exists' => file_exists($app->getBootstrapProvidersPath()),
        'providers_file' => @file_get_contents($app->getBootstrapProvidersPath()) ?: null,
        'view_provider_loaded_pre_handle' => $app->providerIsLoaded(Illuminate\View\ViewServiceProvider::class),
        'view_bound_pre_handle' => $app->bound('view'),
        'view_alias_registered' => $app->getAlias('view'),
        'loaded_providers_pre_handle' => array_keys($app->getLoadedProviders()),
        'deferred_view_provider' => $app->getDeferredServices()['view'] ?? null,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return;
}

try {
    $kernel = $app->make(HttpKernel::class);
    $request = Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');

    echo get_class($e)."\n";
    echo $e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n\n";
    echo "view_bound=".($app->bound('view') ? 'yes' : 'no')."\n";
    echo "view_provider_loaded=".($app->providerIsLoaded(Illuminate\View\ViewServiceProvider::class) ? 'yes' : 'no')."\n";
    echo "config_cached=".($app->configurationIsCached() ? 'yes' : 'no')."\n";
    echo "services_cache=".$app->getCachedServicesPath()."\n";
    echo "config_cache=".$app->getCachedConfigPath()."\n\n";
    echo $e->getTraceAsString()."\n";

    $previous = $e->getPrevious();

    while ($previous) {
        echo "\nCaused by: ".get_class($previous)."\n";
        echo $previous->getMessage()."\n";
        echo $previous->getFile().':'.$previous->getLine()."\n";
        echo $previous->getTraceAsString()."\n";

        $previous = $previous->getPrevious();
    }
}
