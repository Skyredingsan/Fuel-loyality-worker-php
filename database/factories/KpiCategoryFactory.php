<?php

declare(strict_types=1);

namespace Database\Factories;

use FuelPoints\Kpi\Domain\Models\KpiCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KpiCategory>
 */
class KpiCategoryFactory extends Factory
{
    protected $model = KpiCategory::class;

    public function definition(): array
    {
        return [
            'code'        => $this->faker->unique()->lexify('???'),
            'name'        => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}