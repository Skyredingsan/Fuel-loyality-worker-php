<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/**
 * API routes — префикс /api.
 */

// ─── Публичные ───────────────────────────────────────────────
Route::get('/health', [HealthController::class, 'check']);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// ─── Защищённые (нужен JWT) ──────────────────────────────────
Route::middleware('jwt.auth')->group(function (): void {

    // Broadcasting auth
    Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
        return \Illuminate\Support\Facades\Broadcast::auth($request);
    });

    // Auth (protected)
    Route::get('/users/me',           [AuthController::class, 'me']);
    Route::post('/users/refresh',     [AuthController::class, 'refresh'])
        ->middleware('jwt.refresh');
    Route::post('/logout',            [AuthController::class, 'logout']);

    // Users
    Route::get('/users',              [UserController::class, 'index']);
    Route::get('/users/tms',          [UserController::class, 'tms']);
    Route::get('/users/{id}',         [UserController::class, 'show'])
        ->whereNumber('id');

    Route::post('/users/register',    [UserController::class, 'store'])
        ->middleware('role:coordinator');

    Route::put('/users/{id}',         [UserController::class, 'update'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    Route::delete('/users/{id}',      [UserController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    // KPI
    Route::get('/kpi/categories',                          [KpiController::class, 'categories']);
    Route::get('/kpi/indicators',                          [KpiController::class, 'indicators']);
    Route::get('/kpi/categories/{category}/indicators',    [KpiController::class, 'indicatorsByCategory']);

    Route::post('/kpi/indicators',         [KpiController::class, 'store'])
        ->middleware('role:coordinator');

    Route::put('/kpi/indicators/{id}',     [KpiController::class, 'update'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    Route::delete('/kpi/indicators/{id}',  [KpiController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    // Results
    Route::post('/results/enter',          [ResultController::class, 'enter'])
        ->middleware('role:expert,coordinator');

    Route::post('/results/{id}/confirm',   [ResultController::class, 'confirm'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    Route::post('/results/{id}/reject',    [ResultController::class, 'reject'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    Route::delete('/results/{id}',           [ResultController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('role:coordinator');

    Route::get('/results',                 [ResultController::class, 'index']);
    Route::get('/results/my',              [ResultController::class, 'my']);
    Route::get('/results/{id}',            [ResultController::class, 'show'])
        ->whereNumber('id');
    Route::get('/results/{id}/indicators', [ResultController::class, 'detailed'])
        ->whereNumber('id');

    Route::put('/results/{id}',            [ResultController::class, 'update'])
        ->whereNumber('id')
        ->middleware('role:expert,coordinator');

    Route::get('/results/user/{userId}/yearly', [ResultController::class, 'yearly'])
        ->whereNumber('userId');
    Route::get('/results/user/{userId}',        [ResultController::class, 'byUser'])
        ->whereNumber('userId');

    // Levels
    Route::get('/levels',                          [LevelController::class, 'index']);
    Route::get('/levels/user/{userId}',            [LevelController::class, 'currentUserLevel'])
        ->whereNumber('userId');
    Route::get('/levels/user/{userId}/history',    [LevelController::class, 'userHistory'])
        ->whereNumber('userId');

    // Files
    Route::post('/upload',                 [FileController::class, 'upload'])
        ->middleware('role:expert,coordinator');

    Route::get('/upload/{type}/{filename}',  [FileController::class, 'download']);
    Route::delete('/upload/{type}/{filename}', [FileController::class, 'destroy'])
        ->middleware('role:expert,coordinator');

    // Скачать прикреплённый документ (эксперт/координатор)
    Route::get('/uploads/{type}/{filename}', function (string $type, string $filename) {
        $path = storage_path('app/public/uploads/' . $type . '/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $filename);
    })->where('type', '[a-z_]+')->where('filename', '[a-zA-Z0-9_.\-]+');

    // Reports (CSV export)
    Route::post('/reports/export',           [ReportController::class, 'export'])
        ->middleware('role:coordinator');
    Route::get('/reports/exports/{id}',      [ReportController::class, 'showExport']);
    Route::get('/reports/exports/{id}/download', [ReportController::class, 'downloadExport']);
});
