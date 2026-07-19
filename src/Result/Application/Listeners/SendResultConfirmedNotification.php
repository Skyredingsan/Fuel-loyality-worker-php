<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Listeners;

use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use FuelPoints\Result\Domain\Events\ResultConfirmed;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use FuelPoints\User\Application\Notifications\ResultConfirmedNotification;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Слушатель: при подтверждении результатов отправляет ТМ email + database notification.
 */
final class SendResultConfirmedNotification implements ShouldQueue
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly LevelRepositoryInterface $levels,
    ) {}

    public function handle(ResultConfirmed $event): void
    {
        $user = $this->users->findById($event->userId);
        if ($user === null) {
            return;
        }

        $level = $this->levels->findById($event->levelId);
        $levelName = $level?->name ?? '—';

        $monthly = MonthlyResult::find($event->monthlyResultId);
        $period = $monthly?->period->format('Y-m') ?? '—';

        $user->notify(new ResultConfirmedNotification(
            monthlyResultId: $event->monthlyResultId,
            period: $period,
            yearlyPoints: $event->yearlyPoints,
            levelName: $levelName,
        ));
    }
}