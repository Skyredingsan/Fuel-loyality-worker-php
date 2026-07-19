<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Application\Actions;

use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;

final readonly class DeleteIndicatorAction
{
    public function __construct(
        private KpiRepositoryInterface $kpi,
    ) {}

    public function execute(int $id): bool
    {
        return $this->kpi->deleteIndicator($id);
    }
}