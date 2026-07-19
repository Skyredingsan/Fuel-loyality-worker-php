<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Queries;

use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Query: детальные результаты по конкретному MonthlyResult.
 *
 * Используется при редактировании черновика экспертом.
 *
 * @return Collection<int, \FuelPoints\Result\Domain\Models\IndicatorResult>
 */
final readonly class GetDetailedResultsQuery
{
    public function __construct(
        private ResultRepositoryInterface $results,
    ) {}

    public function execute(int $monthlyResultId): Collection
    {
        return $this->results->indicatorResults($monthlyResultId);
    }
}