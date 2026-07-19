<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Actions;

use FuelPoints\Result\Domain\Events\ResultRejected;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Action: отклонение результатов координатором (с причиной).
 */
final readonly class RejectResultAction
{
    public function __construct(
        private ResultRepositoryInterface $results,
    ) {}

    public function execute(int $resultId, string $reason): void
    {
        DB::transaction(function () use ($resultId, $reason): void {
            $monthly = $this->results->findMonthlyResultById($resultId);
            if ($monthly === null) {
                throw new \DomainException("Result #{$resultId} not found");
            }

            $userId = $monthly->user_id;
            $expertId = $monthly->expert_id ?? 0;
            $period = $monthly->period->format('Y-m');

            $this->results->deleteMonthlyResult($resultId);

            Event::dispatch(new ResultRejected(
                userId: $userId,
                expertId: $expertId,
                reason: $reason,
                period: $period,
            ));
        });
    }
}