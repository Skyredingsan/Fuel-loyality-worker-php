<?php

declare(strict_types=1);

namespace FuelPoints\Result\Infrastructure\Persistence;

use FuelPoints\Result\Domain\Enums\ResultStatus;
use FuelPoints\Result\Domain\Models\IndicatorResult;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Атомарные операции с monthly_results и indicator_results.
 *
 * Важный момент: Postgres поддерживает INSERT ... ON CONFLICT DO UPDATE,
 * что идеально ложится на Go-версию.
 */
final class EloquentResultRepository implements ResultRepositoryInterface
{
    public function findOrCreateMonthlyResult(
        int $userId,
        int $expertId,
        Period $period,
    ): MonthlyResult {
        // Атомарный upsert через INSERT ... ON CONFLICT
        DB::table('monthly_results')->upsert(
            values: [
                'user_id'    => $userId,
                'expert_id'  => $expertId,
                'period'     => $period->firstDay()->format('Y-m-d'),
                'status'     => ResultStatus::DRAFT->value,
                'updated_at' => now(),
            ],
            uniqueBy: ['user_id', 'period'],
            update: ['expert_id', 'updated_at'],
        );

        return MonthlyResult::query()
            ->where('user_id', $userId)
            ->where('period', $period->firstDay()->format('Y-m-d'))
            ->firstOrFail();
    }

    public function findMonthlyResult(int $userId, Period $period): ?MonthlyResult
    {
        return MonthlyResult::query()
            ->where('user_id', $userId)
            ->where('period', $period->firstDay()->format('Y-m-d'))
            ->first();
    }

    public function findMonthlyResultById(int $id): ?MonthlyResult
    {
        return MonthlyResult::query()->find($id);
    }

    public function monthlyResultsByPeriod(Period $period): Collection
    {
        return MonthlyResult::query()
            ->with(['user', 'expert'])
            ->where('period', $period->firstDay()->format('Y-m-d'))
            ->orderBy('user_id')
            ->get();
    }

    public function userResultsForYear(int $userId, int $year): Collection
    {
        $start = "{$year}-01-01";
        $end   = "{$year}-12-31";

        return MonthlyResult::query()
            ->where('user_id', $userId)
            ->whereBetween('period', [$start, $end])
            ->where('status', ResultStatus::CONFIRMED->value)
            ->orderBy('period')
            ->get();
    }

    public function totalPointsForYear(int $userId, int $year): int
    {
        return (int) DB::table('indicator_results')
            ->join('monthly_results', 'indicator_results.monthly_result_id', '=', 'monthly_results.id')
            ->where('monthly_results.user_id', $userId)
            ->where('monthly_results.status', ResultStatus::CONFIRMED->value)
            ->whereYear('monthly_results.period', $year)
            ->sum('indicator_results.calculated_points');
    }

    public function saveIndicatorResult(
        int $monthlyResultId,
        int $indicatorId,
        ?float $factValue,
        int $calculatedPoints,
        ?string $documentUrl = null,
    ): IndicatorResult {
        DB::table('indicator_results')->upsert(
            values: [
                'monthly_result_id'       => $monthlyResultId,
                'indicator_id'            => $indicatorId,
                'fact_value'              => $factValue,
                'calculated_points'       => $calculatedPoints,
                'supporting_document_url' => $documentUrl,
                'updated_at'              => now(),
            ],
            uniqueBy: ['monthly_result_id', 'indicator_id'],
            update: [
                'fact_value',
                'calculated_points',
                'supporting_document_url',
                'updated_at',
            ],
        );

        return IndicatorResult::query()
            ->where('monthly_result_id', $monthlyResultId)
            ->where('indicator_id', $indicatorId)
            ->firstOrFail();
    }

    public function indicatorResults(int $monthlyResultId): Collection
    {
        return IndicatorResult::query()
            ->with(['indicator.category'])
            ->where('monthly_result_id', $monthlyResultId)
            ->orderBy('indicator_id')
            ->get();
    }

    public function deleteIndicatorResults(int $monthlyResultId): void
    {
        DB::table('indicator_results')
            ->where('monthly_result_id', $monthlyResultId)
            ->delete();
    }

    public function confirmMonthlyResult(int $monthlyResultId): bool
    {
        return (bool) MonthlyResult::query()
            ->where('id', $monthlyResultId)
            ->update([
                'status'     => ResultStatus::CONFIRMED->value,
                'updated_at' => now(),
            ]);
    }

    public function deleteMonthlyResult(int $monthlyResultId): bool
    {
        return (bool) MonthlyResult::query()
            ->where('id', $monthlyResultId)
            ->delete();
    }

    public function deleteConfirmedMonthlyResult(int $monthlyResultId): bool
    {
        return $this->deleteMonthlyResult($monthlyResultId);
    }
}
