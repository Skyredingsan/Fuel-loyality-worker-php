<?php

declare(strict_types=1);

use FuelPoints\Level\Domain\Models\Level;
use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(classAndTraits1: RefreshDatabase::class);

beforeEach(closure: function (): void {
    ['user' => $this->coordinator, 'token' => $this->token] = authUser(UserRole::COORDINATOR);

    Level::create([
        'name'                => 'Специалист Трассы',
        'min_points_per_year' => 0,
        'privileges'          => ['bonus' => 'Стандартный пакет'],
    ]);
    Level::create([
        'name'                => 'Тактик Магистрали',
        'min_points_per_year' => 4321,
        'privileges'          => ['bonus' => 'Доплата 20%'],
    ]);
    Level::create([
        'name'                => 'Стратег Гран-при',
        'min_points_per_year' => 8642,
        'privileges'          => ['bonus' => 'Доплата 50%', 'prize' => 'Кубинская поездка'],
    ]);
});

it(description: 'lists all levels', closure: function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/levels')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it(description: 'returns lowest level for user without history', closure: function (): void {
    ['user' => $tm] = authUser(UserRole::TM);

    $this->withHeaders(jwtHeader($this->token))
        ->getJson("/api/levels/user/{$tm->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Специалист Трассы');
});

it(description: 'returns empty history for new user', closure: function (): void {
    ['user' => $tm] = authUser(UserRole::TM);

    $this->withHeaders(jwtHeader($this->token))
        ->getJson("/api/levels/user/{$tm->id}/history")
        ->assertOk()
        ->assertJsonCount(0);
});

it(description: 'returns health check without auth', closure: function (): void {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertJsonPath('status', 'OK');
});
