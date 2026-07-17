<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Domain\Repositories;

use FuelPoints\Kpi\Domain\Models\KpiCategory;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use Illuminate\Database\Eloquent\Collection;

interface KpiRepositoryInterface
{
    /**
     * @return Collection<int, KpiCategory>
     */
    public function allCategories(): Collection;

    public function findCategoryByCode(string $code): ?KpiCategory;

    /**
     * All indicators with eager-loaded category.
     *
     * @return Collection<int, KpiIndicator>
     */
    public function allIndicators(): Collection;

    /**
     * @return Collection<int, KpiIndicator>
     */
    public function indicatorsByCategoryCode(string $categoryCode): Collection;

    public function findIndicatorByCode(string $code): ?KpiIndicator;

    public function findIndicatorById(int $id): ?KpiIndicator;

    public function createIndicator(array $data): KpiIndicator;

    public function updateIndicator(int $id, array $data): KpiIndicator;

    public function deleteIndicator(int $id): bool;
}