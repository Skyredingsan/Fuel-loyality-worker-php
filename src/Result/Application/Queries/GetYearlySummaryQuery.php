<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Queries;

use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use FuelPoints\Level\Domain\Services\LevelResolver;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Query: годовой отчёт пользователя.
 *
 * Эквивалент Go GetYearlySummary — все месяцы + итог + уровень.
 *
 * @return array<string, mixed>
 */
final readonly class GetYearlySummaryQuery
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private LevelRepositoryInterface $levels,
    ) {}

    public function execute(int $userId, int $year): array
    {
        $monthly = $this->results->userResultsForYear($userId, $year);
        $totalPoints = $this->results->totalPointsForYear($userId, $year);

        $allLevels = SupportCollection::make($this->levels->all()->all());
        $resolver = new LevelResolver($allLevels);
        $level = $resolver->resolve($totalPoints);

        return [
            'user_id'      => $userId,
            'year'         => $year,
            'total_points' => $totalPoints,
            'level'        => $level?->only(['id', 'name', 'min_points_per_year', 'privileges']),
            'months'       => $monthly->map(fn ($m) => [
                'id'           => $m->id,
                'period'       => $m->period->format('Y-m'),
                'status'       => $m->status->value,
                'expert_fio'   => $m->expert?->fio,
                'month_points' => $this->results->indicatorResults($m->id)
                    ->sum('calculated_points'),
            ])->all(),
        ];
    }
}