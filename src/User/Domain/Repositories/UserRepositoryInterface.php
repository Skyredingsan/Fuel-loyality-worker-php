<?php

declare(strict_types=1);

namespace FuelPoints\User\Domain\Repositories;

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for user persistence.
 *
 * Interface defined in Domain layer — implementation lives in Infrastructure.
 * This lets us swap Eloquent for in-memory in tests.
 */
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @param  UserRole|null  $role  optional filter
     * @return Collection<int, User>
     */
    public function all(?UserRole $role = null): Collection;

    /**
     * Get all users with role=tm (for expert's dropdown).
     *
     * @return Collection<int, User>
     */
    public function allTms(): Collection;

    public function create(array $data): User;

    public function update(int $id, array $data): User;

    public function delete(int $id): bool;

    /**
     * Verify plain password against stored hash.
     */
    public function checkPassword(User $user, string $plainPassword): bool;
}