<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\Actions;

use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use FuelPoints\Result\Application\DTO\EnterResultRequestDto;
use FuelPoints\Result\Domain\Events\ResultsEntered;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Result\Domain\Services\ScoreCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class EnterResultsAction
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private KpiRepositoryInterface $kpi,
        private ScoreCalculator $calculator,
    ) {}

    public function execute(EnterResultRequestDto $dto, int $expertId): MonthlyResult
    {
        return DB::transaction(function () use ($dto, $expertId): MonthlyResult {
            // Проверяем, нет ли уже подтверждённого отчёта
            $existing = $this->results->findMonthlyResult($dto->userId, $dto->period);
            if ($existing !== null && $existing->status === \FuelPoints\Result\Domain\Enums\ResultStatus::CONFIRMED) {
                throw new \DomainException('Невозможно ввести результаты: отчёт за этот период уже подтверждён. Для изменения удалите его.');
            }

            // Находим или создаём ОДИН общий отчёт за месяц
            $monthlyResult = $this->results->findOrCreateMonthlyResult(
                userId: $dto->userId,
                expertId: $expertId,
                period: $dto->period,
            );

            $allIndicators = $this->kpi->allIndicators();
            $indicatorMap = [];
            foreach ($allIndicators as $ind) {
                $indicatorMap[$ind->code] = $ind;
            }

            // Сохраняем ТОЛЬКО те показатели, которые пришли в запросе.
            // Чужие показатели НЕ затираются!
            foreach ($dto->results as $input) {
                if (!isset($indicatorMap[$input->indicatorCode])) {
                    throw new \DomainException("Indicator with code '{$input->indicatorCode}' not found");
                }

                $indicator = $indicatorMap[$input->indicatorCode];
                $points = $this->calculator->calculate($indicator, $input->factValue);

                $this->results->saveIndicatorResult(
                    monthlyResultId: $monthlyResult->id,
                    indicatorId: $indicator->id,
                    factValue: $input->factValue,
                    calculatedPoints: $points->value,
                    documentUrl: $input->documentUrl,
                );
            }

            Event::dispatch(new ResultsEntered(
                monthlyResultId: $monthlyResult->id,
                userId: $dto->userId,
                expertId: $expertId,
                period: $dto->period,
            ));

            return $monthlyResult->fresh(['user', 'expert']);
        });
    }
}