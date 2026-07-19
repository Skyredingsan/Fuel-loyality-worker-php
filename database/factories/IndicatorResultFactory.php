<?php

declare(strict_types=1);

namespace Database\Factories;

use FuelPoints\Result\Domain\Models\IndicatorResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndicatorResult>
 */
class IndicatorResultFactory extends Factory
{
    protected $model = IndicatorResult::class;

    public function definition(): array
    {
        return [
            'monthly_result_id'       => \FuelPoints\Result\Domain\Models\MonthlyResult::factory(),
            'indicator_id'            => \FuelPoints\Kpi\Domain\Models\KpiIndicator::factory(),
            'fact_value'              => $this->faker->randomFloat(2, 0, 100),
            'calculated_points'       => $this->faker->numberBetween(0, 100),
            'supporting_document_url' => null,
        ];
    }
}