<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Queries;

use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;

/**
 * Query: получить MonthlyResult по ID (с отношениями).
 *
 * @return array<string, mixed>|null
 */
final readonly class GetResultByIdQuery
{
    public function __construct(
        private ResultRepositoryInterface $results,
    ) {}

    public function execute(int $resultId): ?array
    {
        $monthly = $this->results->findMonthlyResultById($resultId);
        if ($monthly === null) {
            return null;
        }

        $indicators = $this->results->indicatorResults($resultId);

        return [
            'id'         => $monthly->id,
            'user_id'    => $monthly->user_id,
            'user'       => $monthly->user?->only(['id', 'fio', 'email', 'role', 'cluster_name']),
            'period'     => $monthly->period->format('Y-m'),
            'status'     => $monthly->status->value,
            'indicators' => $indicators->map(fn ($r) => [
                'id'                       => $r->id,
                'indicator_id'             => $r->indicator_id,
                'indicator_code'           => $r->indicator?->code,
                'indicator_name'           => $r->indicator?->name,
                'indicator_type'           => $r->indicator?->indicator_type->value,
                'fact_value'               => $r->fact_value,
                'calculated_points'        => $r->calculated_points,
                'supporting_document_url'  => $r->supporting_document_url,
            ])->all(),
            'created_at' => $monthly->created_at?->toIso8601String(),
            'updated_at' => $monthly->updated_at?->toIso8601String(),
        ];
    }
}