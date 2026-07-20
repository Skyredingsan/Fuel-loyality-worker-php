<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Actions;

use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Action: удаление результата (черновика или подтверждённого).
 * Только для координатора.
 */
final readonly class DeleteResultAction
{
    public function __construct(
        private ResultRepositoryInterface $results,
    ) {}

    public function execute(int $resultId): void
    {
        DB::transaction(function () use ($resultId): void {
            $monthly = $this->results->findMonthlyResultById($resultId);
            if ($monthly === null) {
                throw new \DomainException("Result #{$resultId} not found");
            }

            $this->results->deleteMonthlyResult($resultId);
        });
    }
}
