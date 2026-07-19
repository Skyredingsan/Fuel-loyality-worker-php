<?php

declare(strict_types=1);

namespace FuelPoints\Shared\Infrastructure\Eloquent;

use FuelPoints\Shared\Domain\ValueObjects\Privileges;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent Cast для Privileges Value Object.
 *
 * Отделён от самого VO в Infrastructure-сли — это позволяет VO оставаться
 * чистым (без знания про Eloquent), а cast — переиспользоваться при необходимости.
 */
final class PrivilegesCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?Privileges
    {
        return $value !== null && $value !== ''
            ? Privileges::fromJson($value)
            : null;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Privileges) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return Privileges::fromArray($value)->toJson();
        }

        // Если уже строка (например, при upsert) — отдаём как есть
        return (string) $value;
    }
}