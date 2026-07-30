<?php

declare(strict_types=1);

use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Kpi\Domain\Models\KpiCategory;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(classAndTraits1: RefreshDatabase::class);

beforeEach(closure: function (): void {
    // Фейкаем диски для экспорта
    Storage::fake(disk: 'exports');
    Storage::fake(disk: 'uploads');

    ['user' => $this->coordinator, 'token' => $this->token] = authUser(UserRole::COORDINATOR);

    // Создаём минимальные данные для экспорта
    $category = KpiCategory::create([
        'code'        => 'ПМ',
        'name'        => 'Продажи и маржа',
        'description' => 'Тестовая категория',
    ]);

    KpiIndicator::create([
        'category_id'     => $category->id,
        'code'            => 'ПМ1',
        'name'            => 'Выполнение плана',
        'description'     => 'Тест',
        'unit'            => '%',
        'indicator_type'  => IndicatorType::BASE->value,
        'base_value'      => 90.0,
        'base_weight'     => 50,
    ]);

    // Вводим результат
    ['user' => $tm] = authUser(UserRole::TM);
    $this->tm = $tm;

    ['token' => $expertToken] = authUser(UserRole::EXPERT);

    $this->withHeaders(jwtHeader($expertToken))
        ->postJson('/api/results/enter', [
            'user_id' => $tm->id,
            'period'  => '2026-07',
            'results' => [
                ['indicator_code' => 'ПМ1', 'fact_value' => 95.0],
            ],
        ]);
});

it(description: 'starts export and returns export_id', closure: function (): void {
    $response = $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/reports/export', [
            'period' => '2026-07',
        ]);

    $response->assertAccepted()
        ->assertJsonStructure(['success', 'export_id', 'status', 'period', 'check_url'])
        ->assertJsonPath('period', '2026-07')
        ->assertJsonPath('status', 'pending');  // ← первоначальный статус

    // С sync queue Job уже выполнен — проверим, что статус стал ready
    $exportId = $response->json('export_id');
    $export = \FuelPoints\Report\Domain\Models\CsvExport::find($exportId);
    expect(value: $export->status)->toBe('ready');
    expect(value: $export->file_path)->not->toBeNull();
});

it(description: 'validates period for export', closure: function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/reports/export', ['period' => 'invalid'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['period']);
});

it(description: 'prevents non-coordinator from exporting', closure: function (): void {
    ['token' => $expertToken] = authUser(UserRole::EXPERT);

    $this->withHeaders(jwtHeader($expertToken))
        ->postJson('/api/reports/export', ['period' => '2026-07'])
        ->assertForbidden();
});

it(description: 'shows export status by ID', closure: function (): void {
    $exportResponse = $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/reports/export', ['period' => '2026-07']);

    $exportId = $exportResponse->json('export_id');

    // С sync queue Job уже выполнен — статус ready
    $this->withHeaders(jwtHeader($this->token))
        ->getJson("/api/reports/exports/{$exportId}")
        ->assertOk()
        ->assertJsonPath('id', $exportId)
        ->assertJsonPath('status', 'ready');
});

it(description: 'returns 404 for unknown export ID', closure: function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/reports/exports/non-existent-uuid')
        ->assertNotFound();
});
