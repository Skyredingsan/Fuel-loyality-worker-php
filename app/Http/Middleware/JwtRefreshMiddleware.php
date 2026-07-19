<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Middleware: выпускает новый JWT на основе старого (если он ещё валиден).
 *
 * Используется на эндпоинте POST /api/users/refresh.
 */
final class JwtRefreshMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            $request->attributes->set('new_jwt_token', $newToken);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot refresh token: '.$e->getMessage(),
                'code'    => 401,
            ], 401);
        }

        return $next($request);
    }
}