<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// API и статика обрабатываются в routes/api.php и nginx
// SPA fallback — отдаём index.html из public/
Route::get('/{any}', function () {
    $path = public_path('index.html');

    if (!file_exists($path)) {
        return response()->json([
            'name'   => 'Топливный Альянс API',
            'status' => 'running',
            'docs'   => '/swagger-ui.html',
            'health' => '/api/health',
        ]);
    }

    return response(file_get_contents($path))->header('Content-Type', 'text/html');
})->where('any', '^(?!api|docs|swagger-ui|openapi|uploads|storage|assets).*$');