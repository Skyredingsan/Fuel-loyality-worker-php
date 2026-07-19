<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие: координатор отклонил результаты (с причиной).
 *
 * Слушатели:
 *   - SendResultRejectedNotification (уведомляет ТМ и эксперта)
 */
final class ResultRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $expertId,
        public readonly string $reason,
        public readonly string $period,
    ) {}
}