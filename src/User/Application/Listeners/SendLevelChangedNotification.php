<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\Listeners;

use FuelPoints\Level\Domain\Events\LevelChanged;
use FuelPoints\User\Application\Notifications\LevelChangedNotification;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendLevelChangedNotification implements ShouldQueue
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function handle(LevelChanged $event): void
    {
        $user = $this->users->findById($event->userId);
        $user?->notify(new LevelChangedNotification(
            newLevelName: $event->newLevelName,
            yearlyPoints: $event->yearlyPoints,
        ));
    }
}