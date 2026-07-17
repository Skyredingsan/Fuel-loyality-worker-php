<?php

declare(strict_types=1);

namespace FuelPoints\User\Infrastructure\Persistence;

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of UserRepositoryInterface.
 *
 * Поскольку 30 юзеров — pagination тут не нужен, возвращаем Collection.
 * Hasher инжектируем, чтобы можно было подменить в тестах.
 */
final class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly Hasher $hasher,
    ) {}

    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function all(?UserRole $role = null): Collection
    {
        $q = User::query()->orderBy('fio');

        if ($role !== null) {
            $q->where('role', $role->value);
        }

        return $q->get();
    }

    public function allTms(): Collection
    {
        return User::query()
            ->where('role', UserRole::TM->value)
            ->orderBy('fio')
            ->get();
    }

    public function create(array $data): User
    {
        $data['password_hash'] = $this->hasher->make($data['password']);
        unset($data['password']);

        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->findById($id);
        if ($user === null) {
            throw new \DomainException("User #{$id} not found");
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $data['password_hash'] = $this->hasher->make($data['password']);
        }
        unset($data['password']);

        $user->update($data);
        $user->refresh();

        return $user;
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if ($user === null) {
            return false;
        }

        return $user->delete();
    }

    public function checkPassword(User $user, string $plainPassword): bool
    {
        return $this->hasher->check($plainPassword, $user->password_hash);
    }
}