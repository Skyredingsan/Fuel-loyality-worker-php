<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            channels: __DIR__.'/../routes/channels.php',  // ← добавить
            health: '/up',
        )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware aliases — используем в routes как 'jwt.auth', 'role', etc.
        $middleware->alias([
            'jwt.auth'    => \App\Http\Middleware\JwtAuthMiddleware::class,
            'jwt.refresh' => \App\Http\Middleware\JwtRefreshMiddleware::class,
            'role'        => \App\Http\Middleware\RequireRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JWT-исключения → единый JSON-ответ 401
        $exceptions->renderable(\App\Exceptions\JwtExceptionHandler::render(...));
    })
    ->create();