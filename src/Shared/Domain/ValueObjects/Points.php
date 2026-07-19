<?php

declare(strict_types=1);

namespace FuelPoints\Shared\Domain\ValueObjects;

/**
 * Value Object «Баллы».
 *
 * Инкапсулирует расчётные очки KPI. Может быть отрицательным (для penalty).
 */
final readonly class Points
{
    public function __construct(public int $value = 0)
    {
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromFloat(float $value): self
    {
        return new self((int) round($value));
    }
}