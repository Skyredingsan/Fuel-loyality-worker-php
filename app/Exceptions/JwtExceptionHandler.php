<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

/**
 * Преобразует JWT-исключения в единый JSON-ответ.
 *
 * Зарегистрирован в bootstrap/app.php -> withExceptions().
 */
final class JwtExceptionHandler
{
    public static function render(Throwable $e): ?JsonResponse
    {
        // Tymon's middleware бросает UnauthorizedHttpException при отсутствии токена
        if ($e instanceof UnauthorizedHttpException) {
            $message = $e->getMessage() ?: 'Unauthorized';
            // Если есть предыдущее исключение (например, TokenExpiredException), берём его сообщение
            if ($e->getPrevious() !== null) {
                $message = $e->getPrevious()->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'code'    => 401,
            ], 401);
        }

        if ($e instanceof TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired',
                'code'    => 401,
            ], 401);
        }

        if ($e instanceof TokenBlacklistedException) {
            return response()->json([
                'success' => false,
                'message' => 'Token has been blacklisted',
                'code'    => 401,
            ], 401);
        }

        if ($e instanceof TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
                'code'    => 401,
            ], 401);
        }

        // Базовый JWTException (например, "Token not provided")
        if ($e instanceof JWTException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code'    => 401,
            ], 401);
        }

        return null;
    }
}