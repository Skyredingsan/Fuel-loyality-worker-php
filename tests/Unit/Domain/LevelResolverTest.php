<?php

declare(strict_types=1);

use FuelPoints\Level\Domain\Services\LevelResolver;
use Illuminate\Support\Collection;

it('resolves lowest level when points below all thresholds', function (): void {
    $resolver = new LevelResolver(Collection::make([
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect($resolver->resolve(100)->name)->toBe('Специалист');
});

it('resolves middle level when points in middle range', function (): void {
    $resolver = new LevelResolver(Collection::make([
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect($resolver->resolve(5000)->name)->toBe('Тактик');
});

it('resolves highest level when points above all thresholds', function (): void {
    $resolver = new LevelResolver(Collection::make([
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect($resolver->resolve(10000)->name)->toBe('Стратег');
});

it('handles boundary case: points exactly equal threshold', function (): void {
    $resolver = new LevelResolver(Collection::make([
        makeLevel(0, 'Специалист'),
        makeLevel(4321, 'Тактик'),
        makeLevel(8642, 'Стратег'),
    ]));

    expect($resolver->resolve(4321)->name)->toBe('Тактик');
});

it('throws when levels collection is empty', function (): void {
    $resolver = new LevelResolver(Collection::make([]));

    $resolver->resolve(100);
})->throws(RuntimeException::class);