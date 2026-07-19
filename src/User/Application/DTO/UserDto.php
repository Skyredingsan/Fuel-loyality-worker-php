<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\DTO;

use FuelPoints\User\Domain\Enums\UserRole;

/**
 * DTO для создания/обновления пользователя.
 */
final readonly class UserDto
{
    public function __construct(
        public ?int $id,
        public string $email,
        public UserRole $role,
        public string $fio,
        public ?string $clusterName,
        public int $azsCount,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            email: $data['email'],
            role: UserRole::from($data['role']),
            fio: $data['fio'],
            clusterName: $data['cluster_name'] ?? null,
            azsCount: $data['azs_count'] ?? 0,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'email'        => $this->email,
            'role'         => $this->role->value,
            'role_label'   => $this->role->label(),
            'fio'          => $this->fio,
            'cluster_name' => $this->clusterName,
            'azs_count'    => $this->azsCount,
            'created_at'   => $this->createdAt,
            'updated_at'   => $this->updatedAt,
        ];
    }
}