<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name'   => 'Топливный Альянс API',
    'status' => 'running',
    'docs'   => '/docs',
    'health' => '/api/health',
]));