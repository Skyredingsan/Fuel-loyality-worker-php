<?php

declare(strict_types=1);

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Runner Configuration (Pest 3)
|--------------------------------------------------------------------------
*/

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

uses(TestCase::class)
    ->in('Unit');

uses(Illuminate\Foundation\Testing\WithFaker::class)
    ->in('Feature', 'Unit');

// ─── Helpers ────────────────────────────────────────────

/**
 * Создать KpiIndicator с заданными параметрами (без записи в БД).
 */
function makeIndicator(
    \FuelPoints\Kpi\Domain\Enums\IndicatorType $type,
    ?float $baseValue = null,
    ?int $baseWeight = null,
    ?int $extraWeight = null,
    ?int $penaltyWeight = null,
): \FuelPoints\Kpi\Domain\Models\KpiIndicator {
    return new \FuelPoints\Kpi\Domain\Models\KpiIndicator([
        'indicator_type' => $type,
        'base_value'     => $baseValue,
        'base_weight'    => $baseWeight,
        'extra_weight'   => $extraWeight,
        'penalty_weight' => $penaltyWeight,
    ]);
}

/**
 * Создать Level с заданным порогом.
 */
function makeLevel(int $minPoints, string $name): \FuelPoints\Level\Domain\Models\Level
{
    return new \FuelPoints\Level\Domain\Models\Level([
        'name'                => $name,
        'min_points_per_year' => $minPoints,
    ]);
}