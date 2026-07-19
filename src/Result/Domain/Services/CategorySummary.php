<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Services;

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Kpi\Domain\Models\KpiCategory;
use FuelPoints\Result\Domain\Models\IndicatorResult;
use FuelPoints\Shared\Domain\ValueObjects\Points;
use Illuminate\Database\Eloquent\Collection;

/**
 * DTO: сводка по одной категории KPI за период.
 */
final class CategorySummary
{
    public function __construct(
        public readonly string $categoryCode,
        public readonly string $categoryName,
        public readonly Points $basePoints,
        public readonly Points $extraPoints,
        public readonly Points $penaltyPoints,
    ) {}

    public function total(): Points
    {
        return $this->basePoints
            ->add($this->extraPoints)
            ->add($this->penaltyPoints);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'category_code'  => $this->categoryCode,
            'category_name'  => $this->categoryName,
            'base_points'    => $this->basePoints->value,
            'extra_points'   => $this->extraPoints->value,
            'penalty_points' => $this->penaltyPoints->value,
            'total_points'   => $this->total()->value,
        ];
    }

    /**
     * @param  Collection<int, IndicatorResult>  $results
     */
    public static function fromResults(
        KpiCategory $category,
        Collection $results,
    ): self {
        $base    = Points::zero();
        $extra   = Points::zero();
        $penalty = Points::zero();

        foreach ($results as $result) {
            /** @var IndicatorResult $result */
            $indicator = $result->indicator;
            if ($indicator === null || $indicator->category_id !== $category->id) {
                continue;
            }

            $points = new Points($result->calculated_points);
            match ($indicator->indicator_type) {
                IndicatorType::BASE    => $base    = $base->add($points),
                IndicatorType::EXTRA   => $extra   = $extra->add($points),
                IndicatorType::PENALTY => $penalty = $penalty->add($points),
            };
        }

        return new self(
            categoryCode: $category->code,
            categoryName: $category->name,
            basePoints: $base,
            extraPoints: $extra,
            penaltyPoints: $penalty,
        );
    }
}