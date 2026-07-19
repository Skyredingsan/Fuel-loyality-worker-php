<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Services;

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use FuelPoints\Shared\Domain\ValueObjects\Points;

/**
 * Pure domain service: расчёт баллов для одного KPI-показателя.
 *
 * Формулы (полностью повторяют Go ScoreCalculator):
 *   - BASE:    факт >= base_value ? base_weight * 1 : 0
 *   - EXTRA:   round(fact * extra_weight)
 *   - PENALTY: round(fact * penalty_weight)  (penalty_weight < 0)
 *
 * Чистая функция — не трогает БД. Идеально покрывается unit-тестами.
 */
final class ScoreCalculator
{
    public function calculate(KpiIndicator $indicator, ?float $factValue): Points
    {
        if ($factValue === null) {
            return Points::zero();
        }

        return match ($indicator->indicator_type) {
            IndicatorType::BASE    => $this->calculateBase($indicator, $factValue),
            IndicatorType::EXTRA   => $this->calculateExtra($indicator, $factValue),
            IndicatorType::PENALTY => $this->calculatePenalty($indicator, $factValue),
        };
    }

    private function calculateBase(KpiIndicator $indicator, float $factValue): Points
    {
        if ($indicator->base_value === null || $indicator->base_weight === null) {
            return Points::zero();
        }

        // факт >= порога → вес, иначе 0
        return $factValue >= $indicator->base_value
            ? new Points($indicator->base_weight)
            : Points::zero();
    }

    private function calculateExtra(KpiIndicator $indicator, float $factValue): Points
    {
        if ($indicator->extra_weight === null) {
            return Points::zero();
        }

        // round(fact * weight) — для % перевыполнения и для штук
        return Points::fromFloat($factValue * $indicator->extra_weight);
    }

    private function calculatePenalty(KpiIndicator $indicator, float $factValue): Points
    {
        if ($indicator->penalty_weight === null) {
            return Points::zero();
        }

        // round(fact * penalty_weight) — penalty_weight отрицательный
        return Points::fromFloat($factValue * $indicator->penalty_weight);
    }
}