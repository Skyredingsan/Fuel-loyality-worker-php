<?php

declare(strict_types=1);

namespace Database\Seeders;

use FuelPoints\Kpi\Domain\Models\KpiCategory;
use Illuminate\Database\Seeder;

final class KpiCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'ПП',  'name' => 'Выполнение плана продаж',                    'description' => 'Показатели продаж топлива, СТ и телеметрии'],
            ['code' => 'ОЭК', 'name' => 'Операционная эффективность и качество',      'description' => 'Тайный покупатель, горячая линия, маркировка, штрафы'],
            ['code' => 'ЭКЛ', 'name' => 'Эффективность команды и лидерство',          'description' => 'Текучесть, обучение, дисциплина, ПТК'],
            ['code' => 'КБ',  'name' => 'Культура безопасности',                      'description' => 'Травматизм, ПБОТОС, инициативы'],
        ];

        foreach ($categories as $cat) {
            KpiCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
