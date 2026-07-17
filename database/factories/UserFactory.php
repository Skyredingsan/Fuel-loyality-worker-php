<?php

declare(strict_types=1);

namespace Database\Factories;

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email'         => $this->faker->unique()->companyEmail(),
            'password_hash' => Hash::make('password'),
            'role'          => UserRole::TM->value,
            'fio'           => $this->faker->name(),
            'cluster_name'  => $this->faker->optional()->city(),
            'azs_count'     => $this->faker->numberBetween(0, 20),
        ];
    }

    public function coordinator(): static
    {
        return $this->state(fn () => ['role' => UserRole::COORDINATOR->value]);
    }

    public function expert(): static
    {
        return $this->state(fn () => ['role' => UserRole::EXPERT->value]);
    }

    public function tm(): static
    {
        return $this->state(fn () => ['role' => UserRole::TM->value]);
    }
}