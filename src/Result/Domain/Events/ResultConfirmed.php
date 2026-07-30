<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие: координатор подтвердил результаты.
 */
final readonly class ResultConfirmed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $monthlyResultId,
        public int $userId,
        public int $yearlyPoints,
        public int $levelId,
    ) {
    }
}
