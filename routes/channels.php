<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Приватные каналы Reverb для Fuel Points.
|
*/

// Канал пользователя: только сам пользователь или координатор
Broadcast::channel('user.{id}', function ($user, int $id): bool {
    return (int) $user->id === $id
        || $user->role === \FuelPoints\User\Domain\Enums\UserRole::COORDINATOR;
});

// Канал координаторов: только координаторы
Broadcast::channel('coordinators', function ($user): bool {
    return $user->role === \FuelPoints\User\Domain\Enums\UserRole::COORDINATOR;
});