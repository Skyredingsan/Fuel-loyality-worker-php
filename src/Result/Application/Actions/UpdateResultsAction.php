<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Actions;

use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use FuelPoints\Result\Application\DTO\EnterResultRequestDto;
use FuelPoints\Result\Domain\Events\ResultsUpdated;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Result\Domain\Services\ScoreCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class UpdateResultsAction
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private KpiRepositoryInterface $kpi,
        private ScoreCalculator $calculator,
    ) {}

    public function execute(int $resultId, EnterResultRequestDto $dto, int $expertId): MonthlyResult
    {
        return DB::transaction(function () use ($resultId, $dto, $expertId): MonthlyResult {
            $existing = $this->results->findMonthlyResultById($resultId);
            if ($existing === null) {
                throw new \DomainException("Result #{$resultId} not found");
            }

            // ЗАПРЕТ редактирования подтверждённых отчётов
            if ($existing->status === \FuelPoints\Result\Domain\Enums\ResultStatus::CONFIRMED) {
                throw new \DomainException('Невозможно отредактировать: отчёт уже подтверждён. Для изменения удалите его и создайте заново.');
            }

            $this->results->deleteIndicatorResults($resultId);

            $allIndicators = $this->kpi->allIndicators();
            $indicatorMap = [];
            foreach ($allIndicators as $ind) {
                $indicatorMap[$ind->code] = $ind;
            }

            foreach ($dto->results as $input) {
                if (!isset($indicatorMap[$input->indicatorCode])) {
                    throw new \DomainException(
                        "Indicator '{$input->indicatorCode}' not found"
                    );
                }

                $indicator = $indicatorMap[$input->indicatorCode];
                $points = $this->calculator->calculate($indicator, $input->factValue);

                $this->results->saveIndicatorResult(
                    monthlyResultId: $resultId,
                    indicatorId: $indicator->id,
                    factValue: $input->factValue,
                    calculatedPoints: $points->value,
                    documentUrl: $input->documentUrl,
                );
            }

            Event::dispatch(new ResultsUpdated(
                monthlyResultId: $resultId,
                userId: $dto->userId,
                expertId: $expertId,
                period: $dto->period,
            ));

            return $existing->fresh(['user', 'expert']);
        });
    }
}