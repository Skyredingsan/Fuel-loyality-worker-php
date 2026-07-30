<?php

declare(strict_types=1);

namespace FuelPoints\Level\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие: уровень пользователя изменился (повысился).
 */
final readonly class LevelChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public int $oldLevelId,
        public int $newLevelId,
        public string $newLevelName,
        public int $yearlyPoints,
    ) {
    }
}
