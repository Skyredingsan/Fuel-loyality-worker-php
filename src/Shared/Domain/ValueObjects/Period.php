<?php

declare(strict_types=1);

namespace FuelPoints\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Value Object «Период» — месяц в формате YYYY-MM.
 *
 * В БД хранится как DATE (первый день месяца). Этот VO обеспечивает
 * валидацию и преобразование между строковым и date-представлением.
 */
final readonly class Period implements Stringable
{
    private function __construct(
        public int $year,
        public int $month,
    ) {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Month must be 1..12, got {$month}");
        }
        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException("Year must be 2000..2100, got {$year}");
        }
    }

    public static function fromString(string $value): self
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $value, $m)) {
            throw new InvalidArgumentException("Period must be in YYYY-MM format, got '{$value}'");
        }

        return new self((int) $m[1], (int) $m[2]);
    }

    public static function fromDate(\DateTimeInterface $date): self
    {
        return new self((int) $date->format('Y'), (int) $date->format('n'));
    }

    public static function now(): self
    {
        return self::fromDate(new \DateTimeImmutable());
    }

    /**
     * Первый день месяца в формате ISO (для БД).
     */
    public function firstDay(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('%04d-%02d-01', $this->year, $this->month));
    }

    public function next(): self
    {
        return self::fromDate($this->firstDay()->modify('first day of next month'));
    }

    public function previous(): self
    {
        return self::fromDate($this->firstDay()->modify('first day of previous month'));
    }

    public function year(): int
    {
        return $this->year;
    }

    public function equals(self $other): bool
    {
        return $this->year === $other->year && $this->month === $other->month;
    }

    public function __toString(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }
}