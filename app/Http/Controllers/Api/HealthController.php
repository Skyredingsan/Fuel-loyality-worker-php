<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        return response()->json([
            'status'  => 'OK',
            'message' => 'Fuel Points API is running',
            'version' => '2.0.0',
            'php'     => PHP_VERSION,
            'laravel' => app()->version(),
            'time'    => now()->toIso8601String(),
        ]);
    }
}