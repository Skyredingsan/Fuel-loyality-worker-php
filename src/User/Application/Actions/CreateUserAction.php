<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\Actions;

use FuelPoints\User\Application\DTO\UserDto;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;

/**
 * Создание пользователя (только coordinator).
 */
final readonly class CreateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(UserDto $dto, string $password): UserDto
    {
        $existing = $this->users->findByEmail($dto->email);
        if ($existing !== null) {
            throw new \DomainException("User with email '{$dto->email}' already exists");
        }

        $user = $this->users->create([
            'email'        => $dto->email,
            'password'     => $password,
            'role'         => $dto->role->value,
            'fio'          => $dto->fio,
            'cluster_name' => $dto->clusterName,
            'azs_count'    => $dto->azsCount,
        ]);

        return UserDto::fromArray($user->toArray());
    }
}