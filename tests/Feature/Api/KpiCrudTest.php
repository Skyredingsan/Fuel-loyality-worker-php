<?php

declare(strict_types=1);

use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    ['user' => $this->coordinator, 'token' => $this->token] = authUser(UserRole::COORDINATOR);

    // Создаём 4 категории и 28 индикаторов (как в seeder)
    $categories = [
        ['ПМ',  'Продажи и маржа',                       'Показатели продаж'],
        ['ОЭК', 'Операционная эффективность и качество', 'Тайный покупатель'],
        ['ЭКЛ', 'Эффективность команды и лидерство',     'Текучесть'],
        ['КБ',  'Культура безопасности',                 'Травматизм'],
    ];

    foreach ($categories as [$code, $name, $desc]) {
        \FuelPoints\Kpi\Domain\Models\KpiCategory::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'description' => $desc]
        );
    }
});

it('lists all KPI categories', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/kpi/categories')
        ->assertOk()
        ->assertJsonCount(4);
});

it('lists all KPI indicators', function (): void {
    // Создаём 2 индикатора
    KpiIndicator::factory()->count(2)->create();

    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/kpi/indicators')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates new indicator as coordinator', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/kpi/indicators', [
            'category_code'  => 'ПМ',
            'code'           => 'ПМ999',
            'name'           => 'Тестовый показатель',
            'description'    => 'Для теста',
            'unit'           => '%',
            'indicator_type' => 'base',
            'base_value'     => 50.0,
            'base_weight'    => 10,
        ])
        ->assertCreated()
        ->assertJsonPath('code', 'ПМ999')
        ->assertJsonPath('indicator_type', 'base');

    $this->assertDatabaseHas('kpi_indicators', ['code' => 'ПМ999']);
});

it('validates indicator type constraint', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/kpi/indicators', [
            'category_code'  => 'ПМ',
            'code'           => 'BAD1',
            'name'           => 'Bad',
            'unit'           => '%',
            'indicator_type' => 'invalid',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['indicator_type']);
});

it('prevents expert from creating indicator', function (): void {
    ['token' => $expertToken] = authUser(UserRole::EXPERT);

    $this->withHeaders(jwtHeader($expertToken))
        ->postJson('/api/kpi/indicators', [
            'category_code'  => 'ПМ',
            'code'           => 'NEW1',
            'name'           => 'Test',
            'unit'           => '%',
            'indicator_type' => 'base',
            'base_value'     => 1.0,
            'base_weight'    => 1,
        ])
        ->assertForbidden();
});

it('deletes indicator', function (): void {
    $indicator = KpiIndicator::factory()->create();

    $this->withHeaders(jwtHeader($this->token))
        ->deleteJson("/api/kpi/indicators/{$indicator->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('kpi_indicators', ['id' => $indicator->id]);
});