<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие: координатор подтвердил результаты.
 */
final class ResultConfirmed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $monthlyResultId,
        public readonly int $userId,
        public readonly int $yearlyPoints,
        public readonly int $levelId,
    ) {}
}