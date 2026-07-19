<?php

declare(strict_types=1);

namespace FuelPoints\Shared\Domain\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use RuntimeException;

/**
 * Value Object «Привилегии уровня».
 *
 * В БД хранится в JSONB. Этот VO обеспечивает типизированный доступ
 * к полям бонусов (bonus, prize, ...).
 *
 * Чистый Domain-класс — НЕ знает про Eloquent. Преобразование в/из БД
 * делается отдельным классом PrivilegesCast в Infrastructure.
 */
final class Privileges implements Arrayable, JsonSerializable
{
    /**
     * @param array<string, mixed> $items
     */
    public function __construct(private array $items = [])
    {
    }

    public static function fromJson(?string $json): self
    {
        if ($json === null || $json === '') {
            return new self([]);
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid privileges JSON: '.json_last_error_msg());
        }

        return new self(is_array($data) ? $data : []);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function bonus(): ?string
    {
        return $this->items['bonus'] ?? null;
    }

    public function prize(): ?string
    {
        return $this->items['prize'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function toJson(): string
    {
        return json_encode($this->items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }
}