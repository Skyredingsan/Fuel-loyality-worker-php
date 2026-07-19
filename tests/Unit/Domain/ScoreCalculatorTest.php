<?php

declare(strict_types=1);

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Result\Domain\Services\ScoreCalculator;
use FuelPoints\Shared\Domain\ValueObjects\Period;

beforeEach(function (): void {
    $this->calculator = new ScoreCalculator();
});

// ─── BASE indicators ──────────────────────────────────────────

it('calculates base points when fact >= base_value', function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect($this->calculator->calculate($indicator, 95.0)->value)->toBe(50);
});

it('returns zero when fact < base_value', function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect($this->calculator->calculate($indicator, 89.9)->value)->toBe(0);
});

it('handles boundary: fact exactly equal to base_value', function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect($this->calculator->calculate($indicator, 90.0)->value)->toBe(50);
});

it('returns zero for base indicator without base_weight', function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: null);

    expect($this->calculator->calculate($indicator, 100.0)->value)->toBe(0);
});

// ─── EXTRA indicators ─────────────────────────────────────────

it('calculates extra points as round(fact * weight)', function (): void {
    $indicator = makeIndicator(IndicatorType::EXTRA, extraWeight: 10);

    // round(5.4 * 10) = 54
    expect($this->calculator->calculate($indicator, 5.4)->value)->toBe(54);

    // round(7.555 * 10) = 76
    expect($this->calculator->calculate($indicator, 7.555)->value)->toBe(76);
});

it('returns zero for extra indicator without extra_weight', function (): void {
    $indicator = makeIndicator(IndicatorType::EXTRA, extraWeight: null);

    expect($this->calculator->calculate($indicator, 5.0)->value)->toBe(0);
});

// ─── PENALTY indicators ───────────────────────────────────────

it('calculates penalty points as negative', function (): void {
    $indicator = makeIndicator(IndicatorType::PENALTY, penaltyWeight: -5);

    // round(3 * -5) = -15
    expect($this->calculator->calculate($indicator, 3.0)->value)->toBe(-15);
});

it('returns zero for penalty indicator without penalty_weight', function (): void {
    $indicator = makeIndicator(IndicatorType::PENALTY, penaltyWeight: null);

    expect($this->calculator->calculate($indicator, 3.0)->value)->toBe(0);
});

// ─── Edge cases ───────────────────────────────────────────────

it('returns zero when fact value is null', function (): void {
    $indicator = makeIndicator(IndicatorType::BASE, baseValue: 90.0, baseWeight: 50);

    expect($this->calculator->calculate($indicator, null)->value)->toBe(0);
});

// ─── Period Value Object ──────────────────────────────────────

it('parses valid period string', function (): void {
    $p = Period::fromString('2026-07');

    expect($p->year())->toBe(2026)
        ->and((string) $p)->toBe('2026-07');
});

it('rejects invalid period format', function (): void {
    Period::fromString('2026-13');
})->throws(InvalidArgumentException::class);

it('rejects invalid period string format', function (): void {
    Period::fromString('July 2026');
})->throws(InvalidArgumentException::class);

it('navigates between periods', function (): void {
    $p = Period::fromString('2026-12');

    expect((string) $p->next())->toBe('2027-01')
        ->and((string) $p->previous())->toBe('2026-11');
});