<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Infrastructure\Persistence;

use FuelPoints\Kpi\Domain\Models\KpiCategory;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class EloquentKpiRepository implements KpiRepositoryInterface
{
    public function allCategories(): Collection
    {
        return KpiCategory::query()->orderBy('code')->get();
    }

    public function findCategoryByCode(string $code): ?KpiCategory
    {
        return KpiCategory::query()->where('code', $code)->first();
    }

    public function allIndicators(): Collection
    {
        return KpiIndicator::query()
            ->with('category')
            ->orderBy('code')
            ->get();
    }

    public function indicatorsByCategoryCode(string $categoryCode): Collection
    {
        $category = $this->findCategoryByCode($categoryCode);
        if ($category === null) {
            return new Collection();
        }

        return KpiIndicator::query()
            ->with('category')
            ->where('category_id', $category->id)
            ->orderBy('code')
            ->get();
    }

    public function findIndicatorByCode(string $code): ?KpiIndicator
    {
        return KpiIndicator::query()->where('code', $code)->first();
    }

    public function findIndicatorById(int $id): ?KpiIndicator
    {
        return KpiIndicator::query()->with('category')->find($id);
    }

    public function createIndicator(array $data): KpiIndicator
    {
        $category = $this->findCategoryByCode($data['category_code']);
        if ($category === null) {
            throw new \DomainException("Category '{$data['category_code']}' not found");
        }

        return KpiIndicator::create([
            'category_id'     => $category->id,
            'code'            => $data['code'],
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'unit'            => $data['unit'],
            'indicator_type'  => $data['indicator_type'],
            'base_value'      => $data['base_value'] ?? null,
            'base_weight'     => $data['base_weight'] ?? null,
            'extra_weight'    => $data['extra_weight'] ?? null,
            'penalty_weight'  => $data['penalty_weight'] ?? null,
        ]);
    }

    public function updateIndicator(int $id, array $data): KpiIndicator
    {
        $indicator = $this->findIndicatorById($id);
        if ($indicator === null) {
            throw new \DomainException("Indicator #{$id} not found");
        }

        if (isset($data['category_code'])) {
            $category = $this->findCategoryByCode($data['category_code']);
            if ($category === null) {
                throw new \DomainException("Category '{$data['category_code']}' not found");
            }
            $data['category_id'] = $category->id;
            unset($data['category_code']);
        }

        $indicator->update($data);
        $indicator->refresh();
        $indicator->load('category');

        return $indicator;
    }

    public function deleteIndicator(int $id): bool
    {
        return (bool) KpiIndicator::query()->where('id', $id)->delete();
    }
}