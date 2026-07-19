<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use FuelPoints\User\Application\Actions\CreateUserAction;
use FuelPoints\User\Application\Actions\UpdateUserAction;
use FuelPoints\User\Application\DTO\UserDto;
use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Пользователи
 */
final class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly CreateUserAction $createUser,
        private readonly UpdateUserAction $updateUser,
    ) {}

    /**
     * Список всех пользователей (опционально фильтр по роли).
     */
    public function index(Request $request): JsonResponse
    {
        $role = $request->query('role')
            ? UserRole::tryFrom((string) $request->query('role'))
            : null;

        $users = $this->users->all($role);

        return UserResource::collection($users)->response();
    }

    /**
     * Список ТМ (для дропдауна у эксперта).
     */
    public function tms(): JsonResponse
    {
        $users = $this->users->allTms();

        return UserResource::collection($users)->response();
    }

    /**
     * Получение пользователя по ID.
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->users->findById($id);
        if ($user === null) {
            return $this->error("User #{$id} not found", 404);
        }

        return (new UserResource($user))->response();
    }

    /**
     * Создание пользователя (только координатор).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $dto = UserDto::fromArray($request->validated());
            $password = $request->validated()['password'];
            $result = $this->createUser->execute($dto, $password);

            return response()->json($result->toArray(), 201);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 409);
        }
    }

    /**
     * Обновление пользователя.
     */
    public function update(int $id, UpdateUserRequest $request): JsonResponse
    {
        try {
            $fields = $request->validated();
            $password = $fields['password'] ?? null;
            unset($fields['password']);
            $dto = $this->updateUser->execute($id, $fields, $password);

            return response()->json($dto->toArray());
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Удаление пользователя (нельзя удалить себя).
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $user = \Tymon\JWTAuth\Facades\JWTAuth::user();
        if ($user !== null && (int) $user->id === $id) {
            return $this->error('Cannot delete yourself', 400);
        }

        if (!$this->users->delete($id)) {
            return $this->error("User #{$id} not found", 404);
        }

        return response()->json(null, 204);
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