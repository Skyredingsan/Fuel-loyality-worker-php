<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Application\Actions;

use FuelPoints\Kpi\Application\DTO\KpiIndicatorDto;
use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;

final readonly class UpdateIndicatorAction
{
    public function __construct(
        private KpiRepositoryInterface $kpi,
    ) {}

    public function execute(int $id, KpiIndicatorDto $dto): KpiIndicatorDto
    {
        $indicator = $this->kpi->updateIndicator($id, [
            'category_code'  => $dto->categoryCode,
            'code'           => $dto->code,
            'name'           => $dto->name,
            'description'    => $dto->description,
            'unit'           => $dto->unit,
            'indicator_type' => $dto->indicatorType->value,
            'base_value'     => $dto->baseValue,
            'base_weight'    => $dto->baseWeight,
            'extra_weight'   => $dto->extraWeight,
            'penalty_weight' => $dto->penaltyWeight,
        ]);

        return KpiIndicatorDto::fromArray($indicator->toArray() + [
                'category_code' => $indicator->category?->code,
                'category_name' => $indicator->category?->name,
            ]);
    }
}