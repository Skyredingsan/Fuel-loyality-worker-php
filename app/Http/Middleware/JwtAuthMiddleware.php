<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Middleware: проверяет JWT в заголовке Authorization: Bearer xxx.
 *
 * При неудаче возвращает 401 JSON (не редирект, т.к. это API).
 * При успехе кладёт в request->attributes:
 *   - jwt_user_id
 *   - jwt_role
 *   - jwt_email
 * (используется последующими middleware и контроллерами).
 */
final class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Пропускаем preflight CORS
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $user = JWTAuth::authenticate();

            if ($user === false || $user === null) {
                return $this->unauthorized('User not found');
            }

            $request->attributes->set('jwt_user_id', (int) $payload->get('user_id'));
            $request->attributes->set('jwt_role', $payload->get('role'));
            $request->attributes->set('jwt_email', $payload->get('email'));

        } catch (JWTException $e) {
            return $this->unauthorized('Token is invalid or expired: '.$e->getMessage());
        }

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => 401,
        ], 401);
    }
}