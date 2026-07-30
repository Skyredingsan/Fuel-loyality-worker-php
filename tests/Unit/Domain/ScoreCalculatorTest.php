<?php

declare(strict_types=1);

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Result\Domain\Services\ScoreCalculator;
use FuelPoints\Shared\Domain\ValueObjects\Period;

beforeEach(closure: function (): void {
    $this->calculator = new ScoreCalculator();
});

// ─── BASE indicators ──────────────────────────────────────────

it(description: 'calculates base points when fact >= base_value', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect(value: $this->calculator->calculate($indicator, 95.0)->value)->toBe(50);
});

it(description: 'returns zero when fact < base_value', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect(value: $this->calculator->calculate($indicator, 89.9)->value)->toBe(0);
});

it(description: 'handles boundary: fact exactly equal to base_value', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect(value: $this->calculator->calculate($indicator, 90.0)->value)->toBe(50);
});

it(description: 'returns zero for base indicator without base_weight', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: null);

    expect(value: $this->calculator->calculate($indicator, 100.0)->value)->toBe(0);
});

// ─── EXTRA indicators ─────────────────────────────────────────

it(description: 'calculates extra points as round(fact * weight)', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::EXTRA, extraWeight: 10);

    // round(5.4 * 10) = 54
    expect(value: $this->calculator->calculate($indicator, 5.4)->value)->toBe(54);

    // round(7.555 * 10) = 76
    expect(value: $this->calculator->calculate($indicator, 7.555)->value)->toBe(76);
});

it(description: 'returns zero for extra indicator without extra_weight', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::EXTRA, extraWeight: null);

    expect(value: $this->calculator->calculate($indicator, 5.0)->value)->toBe(0);
});

// ─── PENALTY indicators ───────────────────────────────────────

it(description: 'calculates penalty points as negative', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::PENALTY, penaltyWeight: -5);

    // round(3 * -5) = -15
    expect(value: $this->calculator->calculate($indicator, 3.0)->value)->toBe(-15);
});

it(description: 'returns zero for penalty indicator without penalty_weight', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::PENALTY, penaltyWeight: null);

    expect(value: $this->calculator->calculate($indicator, 3.0)->value)->toBe(0);
});

// ─── Edge cases ───────────────────────────────────────────────

it(description: 'returns zero when fact value is null', closure: function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect(value: $this->calculator->calculate($indicator, null)->value)->toBe(0);
});

// ─── Period Value Object ──────────────────────────────────────

it(description: 'parses valid period string', closure: function (): void {
    $p = Period::fromString(value: '2026-07');

    expect(value: $p->year())->toBe(2026)
        ->and((string) $p)->toBe('2026-07');
});

it(description: 'rejects invalid period format', closure: function (): void {
    Period::fromString(value: '2026-13');
})->throws(exception: InvalidArgumentException::class);

it(description: 'rejects invalid period string format', closure: function (): void {
    Period::fromString(value: 'July 2026');
})->throws(exception: InvalidArgumentException::class);

it(description: 'navigates between periods', closure: function (): void {
    $p = Period::fromString(value: '2026-12');

    expect(value: (string) $p->next())->toBe('2027-01')
        ->and((string) $p->previous())->toBe('2026-11');
});
