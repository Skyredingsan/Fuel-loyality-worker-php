<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Queries;

use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Query: все результаты за период (для дашборда координатора).
 *
 * Возвращает enriched-массив: каждый результат содержит user, expert и
 * список indicator_results. Полный аналог Go GetMonthlyResults.
 *
 * @return array<int, array<string, mixed>>
 */
final readonly class GetMonthlyResultsByPeriodQuery
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private UserRepositoryInterface $users,
    ) {}

    public function execute(Period $period): array
    {
        $monthlyResults = $this->results->monthlyResultsByPeriod($period);
        $enriched = [];

        foreach ($monthlyResults as $monthly) {
            $user   = $this->users->findById($monthly->user_id);
            $expert = $monthly->expert_id
                ? $this->users->findById($monthly->expert_id)
                : null;
            $indicators = $this->results->indicatorResults($monthly->id);

            $enriched[] = [
                'id'         => $monthly->id,
                'period'     => $monthly->period->format('Y-m'),
                'status'     => $monthly->status->value,
                'user'       => $user?->only(['id', 'fio', 'email', 'role', 'cluster_name', 'azs_count']),
                'expert'     => $expert?->only(['id', 'fio', 'email']),
                'indicators' => $indicators->map(fn ($r) => [
                    'id'                    => $r->id,
                    'indicator_id'          => $r->indicator_id,
                    'indicator_code'        => $r->indicator?->code,
                    'indicator_name'        => $r->indicator?->name,
                    'fact_value'            => $r->fact_value,
                    'calculated_points'     => $r->calculated_points,
                    'supporting_document_url' => $r->supporting_document_url,
                ])->all(),
                'created_at' => $monthly->created_at?->toIso8601String(),
            ];
        }

        return $enriched;
    }
}