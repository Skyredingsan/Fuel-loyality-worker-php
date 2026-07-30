<?php

declare(strict_types=1);

use FuelPoints\User\Domain\Enums\UserRole;

it(description: 'has correct enum values', closure: function (): void {
    expect(value: UserRole::TM->value)->toBe('tm')
        ->and(UserRole::EXPERT->value)->toBe('expert')
        ->and(UserRole::COORDINATOR->value)->toBe('coordinator');
});

it(description: 'returns correct labels', closure: function (): void {
    expect(value: UserRole::TM->label())->toBe('Территориальный менеджер')
        ->and(UserRole::EXPERT->label())->toBe('Эксперт')
        ->and(UserRole::COORDINATOR->label())->toBe('Координатор');
});

it(description: 'checks permissions correctly', closure: function (): void {
    expect(value: UserRole::TM->canEnterResults())->toBeFalse()
        ->and(UserRole::EXPERT->canEnterResults())->toBeTrue()
        ->and(UserRole::COORDINATOR->canEnterResults())->toBeTrue()
        ->and(UserRole::EXPERT->canManageSystem())->toBeFalse()
        ->and(UserRole::COORDINATOR->canManageSystem())->toBeTrue()
        ->and(UserRole::COORDINATOR->canConfirmResults())->toBeTrue()
        ->and(UserRole::EXPERT->canConfirmResults())->toBeFalse();
});
