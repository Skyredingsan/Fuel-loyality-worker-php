<?php

declare(strict_types=1);

use FuelPoints\Level\Domain\Services\LevelResolver;
use Illuminate\Support\Collection;

it(description: 'resolves lowest level when points below all thresholds', closure: function (): void {
    $resolver = new LevelResolver(levels: Collection::make(items: [
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect(value: $resolver->resolve(yearlyPoints: 100)->name)->toBe('Специалист');
});

it(description: 'resolves middle level when points in middle range', closure: function (): void {
    $resolver = new LevelResolver(levels: Collection::make(items: [
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect(value: $resolver->resolve(yearlyPoints: 5000)->name)->toBe('Тактик');
});

it(description: 'resolves highest level when points above all thresholds', closure: function (): void {
    $resolver = new LevelResolver(levels: Collection::make(items: [
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect(value: $resolver->resolve(yearlyPoints: 10000)->name)->toBe('Стратег');
});

it(description: 'handles boundary case: points exactly equal threshold', closure: function (): void {
    $resolver = new LevelResolver(levels: Collection::make(items: [
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect(value: $resolver->resolve(yearlyPoints: 4321)->name)->toBe('Тактик');
});

it(description: 'throws when levels collection is empty', closure: function (): void {
    $resolver = new LevelResolver(levels: Collection::make(items: []));

    $resolver->resolve(yearlyPoints: 100);
})->throws(exception: RuntimeException::class);
