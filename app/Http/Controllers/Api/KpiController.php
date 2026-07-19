<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kpi\StoreIndicatorRequest;
use App\Http\Requests\Kpi\UpdateIndicatorRequest;
use App\Http\Resources\KpiIndicatorResource;
use FuelPoints\Kpi\Application\Actions\CreateIndicatorAction;
use FuelPoints\Kpi\Application\Actions\DeleteIndicatorAction;
use FuelPoints\Kpi\Application\Actions\UpdateIndicatorAction;
use FuelPoints\Kpi\Application\DTO\KpiIndicatorDto;
use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * @tags KPI
 */
final class KpiController extends Controller
{
    public function __construct(
        private readonly KpiRepositoryInterface $kpi,
        private readonly CreateIndicatorAction $create,
        private readonly UpdateIndicatorAction $update,
        private readonly DeleteIndicatorAction $delete,
    ) {}

    /**
     * Список всех категорий KPI (ПМ, ОЭК, ЭКЛ, КБ).
     */
    public function categories(): JsonResponse
    {
        return response()->json(
            $this->kpi->allCategories()->map(fn ($c) => [
                'id'          => $c->id,
                'code'        => $c->code,
                'name'        => $c->name,
                'description' => $c->description,
            ])->all()
        );
    }

    /**
     * Список всех показателей KPI.
     */
    public function indicators(): JsonResponse
    {
        return KpiIndicatorResource::collection(
            $this->kpi->allIndicators()
        )->response();
    }

    /**
     * Показатели по коду категории.
     */
    public function indicatorsByCategory(string $category): JsonResponse
    {
        return KpiIndicatorResource::collection(
            $this->kpi->indicatorsByCategoryCode($category)
        )->response();
    }

    /**
     * Создание показателя.
     */
    public function store(StoreIndicatorRequest $request): JsonResponse
    {
        try {
            $dto = KpiIndicatorDto::fromArray($request->validated());
            $indicator = $this->create->execute($dto);

            return response()->json($indicator->toArray(), 201);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 409);
        }
    }

    /**
     * Обновление показателя.
     */
    public function update(int $id, UpdateIndicatorRequest $request): JsonResponse
    {
        try {
            $dto = KpiIndicatorDto::fromArray($request->validated());
            $indicator = $this->update->execute($id, $dto);

            return response()->json($indicator->toArray());
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Удаление показателя.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->delete->execute($id)) {
            return $this->error("Indicator #{$id} not found", 404);
        }

        return response()->json(null, 204);
    }
    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => $status,
        ], $status);
    }
}