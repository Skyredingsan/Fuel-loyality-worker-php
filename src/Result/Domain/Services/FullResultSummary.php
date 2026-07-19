<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Services;

use FuelPoints\Level\Domain\Models\Level;
use FuelPoints\Result\Domain\Models\IndicatorResult;
use FuelPoints\Shared\Domain\ValueObjects\Points;
use Illuminate\Support\Collection;

/**
 * Полный сводный отчёт по результатам ТМ за период.
 *
 * Включает:
 *  - баланс по каждой категории (base + extra + penalty)
 *  - общий итог за период
 *  - суммарные годовые баллы
 *  - актуальный уровень
 *  - детальные результаты по каждому показателю
 */
final class FullResultSummary
{
    /**
     * @param  Collection<int, CategorySummary>  $categories
     * @param  Collection<int, IndicatorResult>  $detailedResults
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $userFio,
        public readonly string $period,
        public readonly Collection $categories,
        public readonly Points $totalPoints,
        public readonly int $yearlyPoints,
        public readonly ?Level $level,
        public readonly Collection $detailedResults,
    ) {}

    public function totalForPeriod(): Points
    {
        $total = Points::zero();
        foreach ($this->categories as $cat) {
            $total = $total->add($cat->total());
        }

        return $total;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'          => $this->userId,
            'user_fio'         => $this->userFio,
            'period'           => $this->period,
            'categories'       => $this->categories->map(fn (CategorySummary $c) => $c->toArray())->all(),
            'total_points'     => $this->totalForPeriod()->value,
            'yearly_points'    => $this->yearlyPoints,
            'level'            => $this->level?->only(['id', 'name', 'min_points_per_year', 'privileges']),
            'detailed_results' => $this->detailedResults->map(fn (IndicatorResult $r) => [
                'id'                    => $r->id,
                'monthly_result_id'     => $r->monthly_result_id,
                'indicator_id'          => $r->indicator_id,
                'fact_value'            => $r->fact_value,
                'calculated_points'     => $r->calculated_points,
                'supporting_document_url' => $r->supporting_document_url,
                'indicator'             => $r->indicator ? [
                    'id'             => $r->indicator->id,
                    'category_id'    => $r->indicator->category_id,
                    'code'           => $r->indicator->code,
                    'name'           => $r->indicator->name,
                    'description'    => $r->indicator->description,
                    'unit'           => $r->indicator->unit,
                    'indicator_type' => $r->indicator->indicator_type->value,
                    'base_value'     => $r->indicator->base_value,
                    'base_weight'    => $r->indicator->base_weight,
                    'extra_weight'   => $r->indicator->extra_weight,
                    'penalty_weight' => $r->indicator->penalty_weight,
                ] : null,
            ])->all(),
        ];
    }

    public static function empty(int $userId, string $period): self
    {
        return new self(
            userId: $userId,
            userFio: '',
            period: $period,
            categories: new Collection(),
            totalPoints: Points::zero(),
            yearlyPoints: 0,
            level: null,
            detailedResults: new Collection(),
        );
    }
}