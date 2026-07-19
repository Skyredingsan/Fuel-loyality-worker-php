<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\Actions;

use FuelPoints\User\Application\DTO\UserDto;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;

final readonly class UpdateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    /**
     * @param array<string, mixed> $fields
     */
    public function execute(int $id, array $fields, ?string $newPassword = null): UserDto
    {
        $user = $this->users->findById($id);
        if ($user === null) {
            throw new \DomainException("User #{$id} not found");
        }

        $data = [];
        if (isset($fields['email']))    $data['email'] = $fields['email'];
        if (isset($fields['role']))     $data['role'] = $fields['role'];
        if (isset($fields['fio']))      $data['fio'] = $fields['fio'];
        if (array_key_exists('cluster_name', $fields)) $data['cluster_name'] = $fields['cluster_name'];
        if (isset($fields['azs_count'])) $data['azs_count'] = (int) $fields['azs_count'];
        if ($newPassword !== null) $data['password'] = $newPassword;

        $user = $this->users->update($id, $data);

        return UserDto::fromArray($user->toArray());
    }
}