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
    ) {
    }

    /**
     * Список всех категорий KPI (ПМ, ОЭК, ЭКЛ, КБ).
     */
    public function categories(): JsonResponse
    {
        return response()->json(
            $this->kpi->allCategories()->map(callback: fn ($c) => [
                'id'          => $c->id,
                'code'        => $c->code,
                'name'        => $c->name,
                'description' => $c->description,
            ])->all()
        );
    }

    public function indicators(): JsonResponse
    {
        $allIndicators = $this->kpi->allIndicators();

        /** @var \FuelPoints\User\Domain\Models\User|null $user */
        $user = \Tymon\JWTAuth\Facades\JWTAuth::user();
        if ($user && $user->role === \FuelPoints\User\Domain\Enums\UserRole::EXPERT) {
            $expertCategories = [];
            foreach (config(key: 'experts') as $catCode => $emails) {
                if (in_array(needle: $user->email, haystack: $emails, strict: true)) {
                    $expertCategories[] = $catCode;
                }
            }
            $allIndicators = $allIndicators->filter(callback: fn ($ind) => in_array(needle: $ind->category?->code, haystack: $expertCategories));
        }

        return KpiIndicatorResource::collection(resource: $allIndicators)->response();
    }

    /**
     * Показатели по коду категории.
     */
    public function indicatorsByCategory(string $category): JsonResponse
    {
        return KpiIndicatorResource::collection(
            resource: $this->kpi->indicatorsByCategoryCode($category)
        )->response();
    }

    /**
     * Создание показателя.
     */
    public function store(StoreIndicatorRequest $request): JsonResponse
    {
        try {
            $dto = KpiIndicatorDto::fromArray(data: $request->validated());
            $indicator = $this->create->execute(dto: $dto);

            return response()->json($indicator->toArray(), 201);
        } catch (\DomainException $e) {
            return $this->error(message: $e->getMessage(), status: 409);
        }
    }

    /**
     * Обновление показателя.
     */
    public function update(int $id, UpdateIndicatorRequest $request): JsonResponse
    {
        try {
            $dto = KpiIndicatorDto::fromArray(data: $request->validated());
            $indicator = $this->update->execute(id: $id, dto: $dto);

            return response()->json($indicator->toArray());
        } catch (\DomainException $e) {
            return $this->error(message: $e->getMessage(), status: 404);
        }
    }

    /**
     * Удаление показателя.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->delete->execute(id: $id)) {
            return $this->error(message: "Indicator #{$id} not found", status: 404);
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
