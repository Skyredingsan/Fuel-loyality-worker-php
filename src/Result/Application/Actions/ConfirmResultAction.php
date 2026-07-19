<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Actions;

use FuelPoints\Level\Domain\Events\LevelChanged;
use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use FuelPoints\Level\Domain\Services\LevelResolver;
use FuelPoints\Result\Domain\Events\ResultConfirmed;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Action: подтверждение результатов координатором.
 */
final readonly class ConfirmResultAction
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private LevelRepositoryInterface $levels,
    ) {}

    public function execute(int $resultId): void
    {
        DB::transaction(function () use ($resultId): void {
            $monthly = $this->results->findMonthlyResultById($resultId);
            if ($monthly === null) {
                throw new \DomainException("Result #{$resultId} not found");
            }

            $this->results->confirmMonthlyResult($resultId);

            // Годовые баллы + уровень
            $year = (int) $monthly->period->format('Y');
            $yearlyPoints = $this->results->totalPointsForYear($monthly->user_id, $year);

            $allLevels = SupportCollection::make(
                $this->levels->all()->all()
            );
            $resolver = new LevelResolver($allLevels);
            $newLevel = $resolver->resolve($yearlyPoints);

            $currentLevel = $this->levels->currentUserLevel($monthly->user_id);
            $oldLevelId = $currentLevel?->id ?? 0;

            if ($currentLevel === null || $currentLevel->id !== $newLevel->id) {
                $this->levels->assignToUser(
                    userId: $monthly->user_id,
                    levelId: $newLevel->id,
                    pointsYear: $yearlyPoints,
                );

                // Диспатчим событие LevelChanged
                Event::dispatch(new LevelChanged(
                    userId: $monthly->user_id,
                    oldLevelId: $oldLevelId,
                    newLevelId: $newLevel->id,
                    newLevelName: $newLevel->name,
                    yearlyPoints: $yearlyPoints,
                ));
            }

            // Диспатчим ResultConfirmed (слушатель отправит уведомление ТМ)
            Event::dispatch(new ResultConfirmed(
                monthlyResultId: $resultId,
                userId: $monthly->user_id,
                yearlyPoints: $yearlyPoints,
                levelId: $newLevel->id,
            ));
        });
    }
}