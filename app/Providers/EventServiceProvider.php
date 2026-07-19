<?php

declare(strict_types=1);

namespace App\Providers;

use FuelPoints\Level\Domain\Events\LevelChanged;
use FuelPoints\Result\Application\Listeners\SendResultConfirmedNotification;
use FuelPoints\Result\Application\Listeners\SendResultRejectedNotification;
use FuelPoints\Result\Domain\Events\ResultConfirmed;
use FuelPoints\Result\Domain\Events\ResultRejected;
use FuelPoints\User\Application\Listeners\SendLevelChangedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Регистрирует listeners для domain events.
 */
final class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ResultConfirmed::class => [
            SendResultConfirmedNotification::class,
        ],
        ResultRejected::class => [
            SendResultRejectedNotification::class,
        ],
        LevelChanged::class => [
            SendLevelChangedNotification::class,
        ],
    ];
}