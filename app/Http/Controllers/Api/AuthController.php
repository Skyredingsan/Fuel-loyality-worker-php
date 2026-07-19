<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @tags Аутентификация
 */
final class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Логин по email и паролю. Возвращает JWT.
     *
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->users->findByEmail($credentials['email']);

        if ($user === null || !$this->users->checkPassword($user, $credentials['password'])) {
            return $this->error('Invalid email or password', 401);
        }

        $token = JWTAuth::fromUser($user);
        $ttl = (int) config('jwt.ttl', 1440);
        $expiresIn = $ttl * 60;

        return response()->json([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn,
            'user'       => [
                'id'           => $user->id,
                'email'        => $user->email,
                'role'         => $user->role->value,
                'role_label'   => $user->role->label(),
                'fio'          => $user->fio,
                'cluster_name' => $user->cluster_name,
                'azs_count'    => $user->azs_count,
            ],
        ]);
    }

    /**
     * Получение текущего пользователя.
     */
    public function me(Request $request): JsonResponse
    {
        $user = JWTAuth::user();

        if ($user === null) {
            return $this->error('User not found', 404);
        }

        return response()->json([
            'id'           => $user->id,
            'email'        => $user->email,
            'role'         => $user->role->value,
            'role_label'   => $user->role->label(),
            'fio'          => $user->fio,
            'cluster_name' => $user->cluster_name,
            'azs_count'    => $user->azs_count,
            'created_at'   => $user->created_at?->toIso8601String(),
            'updated_at'   => $user->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Обновление JWT-токена.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
        } catch (JWTException $e) {
            return $this->error('Cannot refresh: '.$e->getMessage(), 401);
        }

        return response()->json([
            'token'      => $newToken,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl', 1440) * 60,
        ]);
    }

    /**
     * Логаут — инвалидация текущего токена.
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } catch (JWTException $e) {
            return $this->error('Failed to logout: '.$e->getMessage(), 500);
        }
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => $status,
        ], $status);
    }
}