<?php

declare(strict_types=1);

namespace FuelPoints\Level\Domain\Services;

use FuelPoints\Level\Domain\Models\Level;
use Illuminate\Support\Collection;

/**
 * Pure domain service: определяет уровень пользователя по годовому количеству баллов.
 */
final readonly class LevelResolver
{
    /**
     * @param Collection<int, Level> $levels  отсортированы по возрастанию min_points
     */
    public function __construct(
        private Collection $levels,
    ) {
    }

    public function resolve(int $yearlyPoints): Level
    {
        if ($this->levels->isEmpty()) {
            throw new \RuntimeException(message: 'No levels configured in system');
        }

        $matched = $this->levels
            ->filter(callback: fn (Level $level) => $level->min_points_per_year <= $yearlyPoints)
            ->sortByDesc(callback: fn (Level $level) => $level->min_points_per_year)
            ->first();

        return $matched ?? $this->lowest();
    }

    public function lowest(): Level
    {
        return $this->levels->sortBy(callback: fn (Level $l) => $l->min_points_per_year)->first();
    }
}
