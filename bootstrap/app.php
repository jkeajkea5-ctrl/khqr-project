<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminRoleMiddleware;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'admin.role' => AdminRoleMiddleware::class,
            'user' => UserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response, $exception, Request $request) {
            if ($response->getStatusCode() !== 419 || $request->expectsJson()) {
                return $response;
            }

            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()
                    ->route('admin.login')
                    ->with('error', 'Your admin session expired. Please sign in again.');
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', 'Your session expired. Please refresh the page and try again.');
        });
    })->create();
