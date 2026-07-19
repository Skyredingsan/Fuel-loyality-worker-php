<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Middleware: проверяет, что у текущего юзера одна из разрешённых ролей.
 *
 * Использование в routes:
 *   Route::post('users', ...)->middleware(['jwt.auth', 'role:coordinator']);
 *
 * Важно: ставится ПОСЛЕ jwt.auth (Tymon's middleware аутентифицирует юзера).
 */
final class RequireRoleMiddleware
{
    /**
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @param  string  ...$roles  список разрешённых ролей (tm, expert, coordinator)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = JWTAuth::user();

        if ($user === null) {
            return $this->forbidden('Unauthenticated');
        }

        $userRole = $user->role->value;

        if (!in_array($userRole, $roles, true)) {
            return $this->forbidden(
                "Required role: ".implode(' or ', $roles).", got: {$userRole}"
            );
        }

        return $next($request);
    }

    private function forbidden(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => 403,
        ], 403);
    }
}