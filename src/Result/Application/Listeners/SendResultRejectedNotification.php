<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Listeners;

use FuelPoints\Result\Domain\Events\ResultRejected;
use FuelPoints\User\Application\Notifications\ResultRejectedNotification;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendResultRejectedNotification implements ShouldQueue
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function handle(ResultRejected $event): void
    {
        // 1. Уведомляем ТМ
        $tm = $this->users->findById($event->userId);
        if ($tm !== null) {
            $tm->notify(new ResultRejectedNotification(
                period: $event->period,
                reason: $event->reason,
                tmFio: $tm->fio,
            ));
        }

        // 2. Уведомляем эксперта (если есть)
        if ($event->expertId > 0) {
            $expert = $this->users->findById($event->expertId);
            $expert?->notify(new ResultRejectedNotification(
                period: $event->period,
                reason: $event->reason,
                tmFio: $tm?->fio ?? '—',
            ));
        }
    }
}