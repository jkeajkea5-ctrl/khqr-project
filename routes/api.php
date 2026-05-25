<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/diag', function () {
    return response()->json([
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'app_key_present' => filled(config('app.key')),
        'view_compiled_path' => config('view.compiled'),
        'tmp_dir' => sys_get_temp_dir(),
        'tmp_writable' => is_writable(sys_get_temp_dir()),
        'database_default' => config('database.default'),
        'sqlite_exists' => file_exists(database_path('database.sqlite')),
        'sqlite_readable' => is_readable(database_path('database.sqlite')),
        'pdo_sqlite_loaded' => extension_loaded('pdo_sqlite'),
        'sqlite3_loaded' => extension_loaded('sqlite3'),
        'vercel' => env('VERCEL'),
        'vercel_env' => env('VERCEL_ENV'),
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
