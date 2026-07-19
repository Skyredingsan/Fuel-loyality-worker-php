<?php

declare(strict_types=1);

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Kpi\Domain\Models\KpiCategory;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    ['user' => $this->coordinator, 'token' => $this->coordToken] = authUser(UserRole::COORDINATOR);
    ['user' => $this->expert, 'token' => $this->expertToken] = authUser(UserRole::EXPERT);
    ['user' => $this->tm] = authUser(UserRole::TM);

    // Создаём категорию и индикатор для тестов
    $this->category = KpiCategory::create([
        'code'        => 'ПМ',
        'name'        => 'Продажи и маржа',
        'description' => 'Тестовая категория',
    ]);

    $this->indicator = KpiIndicator::create([
        'category_id'     => $this->category->id,
        'code'            => 'ПМ1',
        'name'            => 'Выполнение плана по топливу',
        'description'     => 'Тест',
        'unit'            => '%',
        'indicator_type'  => IndicatorType::BASE->value,
        'base_value'      => 90.0,
        'base_weight'     => 50,
    ]);

    // Создаём уровень
    \FuelPoints\Level\Domain\Models\Level::create([
        'name'                => 'Специалист Трассы',
        'min_points_per_year' => 0,
        'privileges'          => ['bonus' => 'Стандартный пакет'],
    ]);
});

it('expert enters results for a TM', function (): void {
    $response = $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => '2026-07',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $this->tm->id)
        ->assertJsonPath('data.period', '2026-07')
        ->assertJsonPath('data.status', 'draft');

    $this->assertDatabaseHas('monthly_results', [
        'user_id' => $this->tm->id,
        'period'  => '2026-07-01',
    ]);

    $this->assertDatabaseHas('indicator_results', [
        'indicator_id'      => $this->indicator->id,
        'fact_value'        => 95.0,
        'calculated_points' => 50,
    ]);
});

it('rejects result entry from TM role', function (): void {
    ['token' => $tmToken] = authUser(UserRole::TM);

    $this->withHeaders(jwtHeader($tmToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => '2026-07',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ])
        ->assertForbidden();
});

it('validates period format YYYY-MM', function (): void {
    $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => 'July 2026',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['period']);
});

it('rejects non-existent indicator code', function (): void {
    $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => '2026-07',
            'results' => [
                ['indicator_code' => 'NOT_REAL', 'fact_value' => 95.0],
            ],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['results.0.indicator_code']);
});

it('coordinator confirms draft result', function (): void {
    // Сначала вводим
    $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => '2026-08',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ]);

    $monthlyId = \FuelPoints\Result\Domain\Models\MonthlyResult::first()->id;

    $this->withHeaders(jwtHeader($this->coordToken))
        ->postJson("/api/results/{$monthlyId}/confirm")
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('monthly_results', [
        'id'     => $monthlyId,
        'status' => 'confirmed',
    ]);
});

it('coordinator rejects draft result with reason', function (): void {
    $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => '2026-09',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ]);

    $monthlyId = \FuelPoints\Result\Domain\Models\MonthlyResult::first()->id;

    $this->withHeaders(jwtHeader($this->coordToken))
        ->postJson("/api/results/{$monthlyId}/reject", [
            'reason' => 'Данные не соответствуют действительности',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('monthly_results', ['id' => $monthlyId]);
});

it('rejects rejection with empty reason', function (): void {
    $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $this->tm->id,
            'period'  => '2026-10',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ]);

    $monthlyId = \FuelPoints\Result\Domain\Models\MonthlyResult::first()->id;

    $this->withHeaders(jwtHeader($this->coordToken))
        ->postJson("/api/results/{$monthlyId}/reject", ['reason' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

it('TM can fetch own results', function (): void {
    ['user' => $tm, 'token' => $tmToken] = authUser(UserRole::TM);

    $this->withHeaders(jwtHeader($this->expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $tm->id,
            'period'  => '2026-11',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ]);

    $this->withHeaders(jwtHeader($tmToken))
        ->getJson('/api/results/my?period=2026-11')
        ->assertOk()
        ->assertJsonPath('data.user_id', $tm->id)
        ->assertJsonPath('data.period', '2026-11')
        ->assertJsonPath('data.total_points', 50);
});

it('returns empty summary when no results exist', function (): void {
    ['user' => $tm, 'token' => $tmToken] = authUser(UserRole::TM);

    $this->withHeaders(jwtHeader($tmToken))
        ->getJson('/api/results/my?period=2026-12')
        ->assertOk()
        ->assertJsonPath('data.total_points', 0);
});