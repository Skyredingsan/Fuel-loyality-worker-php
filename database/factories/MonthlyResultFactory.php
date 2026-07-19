<?php

declare(strict_types=1);

namespace Database\Factories;

use FuelPoints\Result\Domain\Enums\ResultStatus;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlyResult>
 */
class MonthlyResultFactory extends Factory
{
    protected $model = MonthlyResult::class;

    public function definition(): array
    {
        return [
            'user_id'   => \FuelPoints\User\Domain\Models\User::factory(),
            'expert_id' => \FuelPoints\User\Domain\Models\User::factory(),
            'period'    => $this->faker->dateTimeThisYear()->format('Y-m-01'),
            'status'    => ResultStatus::DRAFT->value,
        ];
    }
}