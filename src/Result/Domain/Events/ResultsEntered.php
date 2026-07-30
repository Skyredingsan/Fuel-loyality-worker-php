<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Events;

use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие: эксперт ввёл результаты за месяц.
 */
final readonly class ResultsEntered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $monthlyResultId,
        public int $userId,
        public int $expertId,
        public Period $period,
    ) {
    }
}
