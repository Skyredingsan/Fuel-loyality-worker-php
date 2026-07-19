<?php

declare(strict_types=1);

namespace FuelPoints\Level\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие: уровень пользователя изменился (повысился).
 */
final class LevelChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $oldLevelId,
        public readonly int $newLevelId,
        public readonly string $newLevelName,
        public readonly int $yearlyPoints,
    ) {}
}