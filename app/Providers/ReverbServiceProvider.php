<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Регистрирует авторизацию для приватных каналов Reverb.
 *
 * Дублирует логику из routes/channels.php — но через ServiceProvider.
 * Laravel 13 поддерживает оба способа (channels.php ИЛИ ReverbServiceProvider).
 */
final class ReverbServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Авторизация приватного канала пользователя
        \Illuminate\Support\Facades\Broadcast::channel('user.{id}', function ($user, int $id): bool {
            return (int) $user->id === $id
                || $user->role === \FuelPoints\User\Domain\Enums\UserRole::COORDINATOR;
        });

        // Авторизация канала координаторов
        \Illuminate\Support\Facades\Broadcast::channel('coordinators', function ($user): bool {
            return $user->role === \FuelPoints\User\Domain\Enums\UserRole::COORDINATOR;
        });
    }
}