<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Events;

use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ResultsUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $monthlyResultId,
        public readonly int $userId,
        public readonly int $expertId,
        public readonly Period $period,
    ) {}
}