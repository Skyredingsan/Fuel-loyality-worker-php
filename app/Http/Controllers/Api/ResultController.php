<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Result\EnterResultRequest;
use App\Http\Requests\Result\RejectResultRequest;
use App\Http\Resources\FullSummaryResource;
use App\Http\Resources\IndicatorResultResource;
use App\Http\Resources\MonthlyResultResource;
use FuelPoints\Result\Application\Actions\ConfirmResultAction;
use FuelPoints\Result\Application\Actions\EnterResultsAction;
use FuelPoints\Result\Application\Actions\RejectResultAction;
use FuelPoints\Result\Application\Actions\UpdateResultsAction;
use FuelPoints\Result\Application\DTO\EnterResultRequestDto;
use FuelPoints\Result\Application\Queries\GetDetailedResultsQuery;
use FuelPoints\Result\Application\Queries\GetFullSummaryQuery;
use FuelPoints\Result\Application\Queries\GetMonthlyResultsByPeriodQuery;
use FuelPoints\Result\Application\Queries\GetResultByIdQuery;
use FuelPoints\Result\Application\Queries\GetYearlySummaryQuery;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @tags Результаты
 */
final class ResultController extends Controller
{
    public function __construct(
        private readonly EnterResultsAction $enterResults,
        private readonly UpdateResultsAction $updateResults,
        private readonly ConfirmResultAction $confirmResult,
        private readonly RejectResultAction $rejectResult,
        private readonly GetFullSummaryQuery $getFullSummary,
        private readonly GetMonthlyResultsByPeriodQuery $getMonthlyResults,
        private readonly GetYearlySummaryQuery $getYearlySummary,
        private readonly GetDetailedResultsQuery $getDetailed,
        private readonly GetResultByIdQuery $getResultById,
        private readonly \FuelPoints\Result\Application\Actions\DeleteResultAction $deleteResult,
    ) {}

    /**
     * Ввод результатов за месяц (для эксперта/координатора).
     */
    public function enter(EnterResultRequest $request): JsonResponse
    {
        $dto = EnterResultRequestDto::fromArray($request->validated());
        $expertId = (int) JWTAuth::user()?->id;

        $result = $this->enterResults->execute($dto, $expertId);

        return (new MonthlyResultResource($result))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Подтверждение результатов координатором.
     */
    public function confirm(int $id): JsonResponse
    {
        try {
            $this->confirmResult->execute($id);

            return response()->json([
                'success' => true,
                'message' => 'Results confirmed successfully',
            ]);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Отклонение результатов (с причиной).
     */
    public function reject(int $id, RejectResultRequest $request): JsonResponse
    {
        try {
            $this->rejectResult->execute($id, $request->validated()['reason']);

            return response()->json([
                'success' => true,
                'message' => 'Results rejected successfully',
                'reason'  => $request->validated()['reason'],
            ]);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Получение результата по ID (для редактирования черновика).
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->getResultById->execute($id);
        if ($result === null) {
            return $this->error("Result #{$id} not found", 404);
        }

        return response()->json($result);
    }

    /**
     * Обновление черновика.
     */
    public function update(int $id, EnterResultRequest $request): JsonResponse
    {
        try {
            $dto = EnterResultRequestDto::fromArray($request->validated());
            $expertId = (int) JWTAuth::user()?->id;
            $result = $this->updateResults->execute($id, $dto, $expertId);

            return response()->json([
                'success' => true,
                'message' => 'Results updated successfully',
                'data'    => new MonthlyResultResource($result),
            ]);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Мои результаты (для ТМ).
     */
    public function my(Request $request): JsonResponse
    {
        $userId = (int) JWTAuth::user()?->id;
        $periodStr = $request->query('period');
        $period = $periodStr
            ? Period::fromString($periodStr)
            : Period::now();

        $summary = $this->getFullSummary->execute($userId, $period);

        return (new FullSummaryResource($summary))->response();
    }

    /**
     * Результаты конкретного ТМ за период.
     */
    public function byUser(int $userId, Request $request): JsonResponse
    {
        $periodStr = (string) $request->query('period', '');
        if ($periodStr === '') {
            return $this->error('Period parameter is required (YYYY-MM)', 400);
        }

        $summary = $this->getFullSummary->execute($userId, Period::fromString($periodStr));

        return (new FullSummaryResource($summary))->response();
    }

    /**
     * Все результаты за период (дашборд координатора).
     */
    public function index(Request $request): JsonResponse
    {
        $periodStr = (string) $request->query('period', '');
        if ($periodStr === '') {
            return $this->error('Period parameter is required (YYYY-MM)', 400);
        }

        $results = $this->getMonthlyResults->execute(Period::fromString($periodStr));

        return response()->json($results);
    }

    /**
     * Детальные результаты по MonthlyResult.
     */
    public function detailed(int $id): JsonResponse
    {
        $results = $this->getDetailed->execute($id);

        return IndicatorResultResource::collection($results)->response();
    }

    /**
     * Удаление результата (coordinator).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteResult->execute($id);
            return response()->json(null, 204);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Годовой отчёт пользователя.
     */
    public function yearly(int $userId, Request $request): JsonResponse
    {
        $year = (int) ($request->query('year') ?? now()->year);
        $summary = $this->getYearlySummary->execute($userId, $year);

        return response()->json($summary);
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
