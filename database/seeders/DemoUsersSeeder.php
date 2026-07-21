<?php

declare(strict_types=1);

namespace Database\Seeders;

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Демонстрационные пользователи для разработки и тестирования.
 * НЕ содержит реальных ФИО или email сотрудников.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email'        => 'coordinator@demo.fuel',
                'password'     => 'DemoCoord#2026',
                'role'         => UserRole::COORDINATOR,
                'fio'          => 'Координаторов Координатор Координаторович',
                'cluster_name' => 'Центральный офис',
                'azs_count'    => 0,
            ],
            [
                'email'        => 'expert@demo.fuel',
                'password'     => 'DemoExpert#2026',
                'role'         => UserRole::EXPERT,
                'fio'          => 'Экспертов Эксперт Экспертович',
                'cluster_name' => 'Отдел аналитики',
                'azs_count'    => 0,
            ],
            [
                'email'        => 'tm@demo.fuel',
                'password'     => 'DemoTM#2026',
                'role'         => UserRole::TM,
                'fio'          => 'Менеджеров Менеджер Менеджерович',
                'cluster_name' => 'Тестовый куст',
                'azs_count'    => 5,
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role'          => $data['role']->value,
                    'fio'           => $data['fio'],
                    'cluster_name'  => $data['cluster_name'],
                    'azs_count'     => $data['azs_count'],
                    'password_hash' => Hash::make($data['password']),
                ]
            );
        }
    }
}