<?php

declare(strict_types=1);

namespace Database\Seeders;

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 2 координатора из ensureUsers (пароли заменены на demo-пароли).
 */
final class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email'        => 'VelichkinaSV@fuel.ru',
                'password'     => 'DemoCoord#2026',
                'role'         => UserRole::COORDINATOR,
                'fio'          => 'Величкина Светлана Владимировна',
                'cluster_name' => 'Центральный офис',
                'azs_count'    => 0,
            ],
            [
                'email'        => 'ValeevDI@fuel.ru',
                'password'     => 'DemoCoord#2026',
                'role'         => UserRole::COORDINATOR,
                'fio'          => 'Валеев Денис Игоревич',
                'cluster_name' => 'Центральный офис',
                'azs_count'    => 0,
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