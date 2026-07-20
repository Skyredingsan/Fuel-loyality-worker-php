<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Repositories;

use FuelPoints\Result\Domain\Models\IndicatorResult;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Database\Eloquent\Collection;

interface ResultRepositoryInterface
{
    /**
     * Find or create a monthly result for user/period (atomic upsert).
     * Mirrors Go's `ON CONFLICT (user_id, period) DO UPDATE`.
     */
    public function findOrCreateMonthlyResult(
        int $userId,
        int $expertId,
        Period $period,
    ): MonthlyResult;

    public function findMonthlyResult(int $userId, Period $period): ?MonthlyResult;

    public function findMonthlyResultById(int $id): ?MonthlyResult;

    /**
     * @return Collection<int, MonthlyResult>
     */
    public function monthlyResultsByPeriod(Period $period): Collection;

    /**
     * @return Collection<int, MonthlyResult>
     */
    public function userResultsForYear(int $userId, int $year): Collection;

    /**
     * Sum of calculated_points for confirmed results in the given year.
     */
    public function totalPointsForYear(int $userId, int $year): int;

    /**
     * Upsert one indicator result (atomic).
     */
    public function saveIndicatorResult(
        int $monthlyResultId,
        int $indicatorId,
        ?float $factValue,
        int $calculatedPoints,
        ?string $documentUrl = null,
    ): IndicatorResult;

    /**
     * @return Collection<int, IndicatorResult>  with eager-loaded indicator.category
     */
    public function indicatorResults(int $monthlyResultId): Collection;

    /**
     * Delete all indicator results for a monthly result (before re-entering).
     */
    public function deleteIndicatorResults(int $monthlyResultId): void;

    public function confirmMonthlyResult(int $monthlyResultId): bool;

    public function deleteMonthlyResult(int $monthlyResultId): bool;

        /**
     * Delete a confirmed result (only coordinator).
     */
    public function deleteConfirmedMonthlyResult(int $monthlyResultId): bool;
}
