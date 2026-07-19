<?php

declare(strict_types=1);

namespace Database\Seeders;

use FuelPoints\Level\Domain\Models\Level;
use Illuminate\Database\Seeder;

/**
 * Сиды уровней — 3 уровня привилегий.
 *
 * Исправлен "баг" Go-версии, где у 2-го и 3-го уровней был одинаковый порог 4321.
 * Здесь разные пороги — чтобы LevelResolver работал корректно.
 */
final class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'name'                => 'Специалист Трассы',
                'min_points_per_year' => 0,
                'privileges'          => ['bonus' => 'Стандартный пакет мотивации'],
            ],
            [
                'name'                => 'Тактик Магистрали',
                'min_points_per_year' => 4321,
                'privileges'          => ['bonus' => 'Доплата 20% к окладу'],
            ],
            [
                'name'                => 'Стратег Гран-при',
                'min_points_per_year' => 8642,
                'privileges'          => [
                    'bonus' => 'Доплата 50% к окладу',
                    'prize' => 'Поездка на Кубу',
                ],
            ],
        ];

        foreach ($levels as $data) {
            Level::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}