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
            ['code' => 'ПМ',  'name' => 'Продажи и маржа',                              'description' => 'Показатели продаж топлива, СТ и маржинальности'],
            ['code' => 'ОЭК', 'name' => 'Операционная эффективность и качество',        'description' => 'Тайный покупатель, аудиты, штрафы'],
            ['code' => 'ЭКЛ', 'name' => 'Эффективность команды и лидерство',            'description' => 'Текучесть, обучение, дисциплина'],
            ['code' => 'КБ',  'name' => 'Культура безопасности',                        'description' => 'Травматизм, ПБОТОС, инициативы'],
        ];

        foreach ($categories as $cat) {
            KpiCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
    }
}