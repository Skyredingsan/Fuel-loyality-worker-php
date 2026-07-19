<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Queries;

use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use FuelPoints\Level\Domain\Services\LevelResolver;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Result\Domain\Services\CategorySummary;
use FuelPoints\Result\Domain\Services\FullResultSummary;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use FuelPoints\Shared\Domain\ValueObjects\Points;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Query: полный сводный отчёт ТМ за период.
 */
final readonly class GetFullSummaryQuery
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private KpiRepositoryInterface $kpi,
        private UserRepositoryInterface $users,
        private LevelRepositoryInterface $levels,
    ) {}

    public function execute(int $userId, Period $period): FullResultSummary
    {
        $user = $this->users->findById($userId);
        $monthly = $this->results->findMonthlyResult($userId, $period);

        if ($user === null || $monthly === null) {
            return FullResultSummary::empty($userId, (string) $period);
        }

        // 1. Детальные результаты
        $detailed = $this->results->indicatorResults($monthly->id);

        // 2. Сводка по каждой категории (возвращает Support\Collection после map)
        $categories = $this->kpi->allCategories()
            ->map(fn ($cat) => CategorySummary::fromResults($cat, $detailed));

        // 3. Годовой баланс + уровень
        $year = $period->year();
        $yearlyPoints = $this->results->totalPointsForYear($userId, $year);

        $allLevels = SupportCollection::make($this->levels->all()->all());
        $resolver = new LevelResolver($allLevels);
        $level = $resolver->resolve($yearlyPoints);

        // 4. Общий итог за период
        $totalForPeriod = $this->calculateTotalForPeriod($categories);

        return new FullResultSummary(
            userId: $userId,
            userFio: $user->fio,
            period: (string) $period,
            categories: $categories,
            totalPoints: $totalForPeriod,
            yearlyPoints: $yearlyPoints,
            level: $level,
            detailedResults: $detailed,
        );
    }

    /**
     * @param  SupportCollection<int, CategorySummary>  $categories
     */
    private function calculateTotalForPeriod(SupportCollection $categories): Points
    {
        $total = Points::zero();
        foreach ($categories as $cat) {
            $total = $total->add($cat->total());
        }
        return $total;
    }
}