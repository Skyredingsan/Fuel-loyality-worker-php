<?php

declare(strict_types=1);

namespace Database\Factories;

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Kpi\Domain\Models\KpiCategory;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KpiIndicator>
 */
class KpiIndicatorFactory extends Factory
{
    protected $model = KpiIndicator::class;

    public function definition(): array
    {
        $category = KpiCategory::inRandomOrder()->first()
            ?? KpiCategory::factory()->create();

        return [
            'category_id'     => $category->id,
            'code'            => 'TEST_' . $this->faker->unique()->numberBetween(1000, 9999),
            'name'            => $this->faker->words(3, true),
            'description'     => $this->faker->sentence(),
            'unit'            => $this->faker->randomElement(['%', 'шт', 'чел']),
            'indicator_type'  => IndicatorType::BASE->value,
            'base_value'      => 90.0,
            'base_weight'     => 50,
            'extra_weight'    => null,
            'penalty_weight'  => null,
        ];
    }
}