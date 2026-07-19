<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LevelResource;
use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * @tags Уровни
 */
final class LevelController extends Controller
{
    public function __construct(
        private readonly LevelRepositoryInterface $levels,
    ) {}

    /**
     * Список всех уровней.
     */
    public function index(): JsonResponse
    {
        return LevelResource::collection(
            $this->levels->all()
        )->response();
    }

    /**
     * Текущий уровень пользователя.
     */
    public function currentUserLevel(int $userId): JsonResponse
    {
        $level = $this->levels->currentUserLevel($userId)
            ?? $this->levels->lowest();

        return (new LevelResource($level))->response();
    }

    /**
     * История уровней пользователя.
     */
    public function userHistory(int $userId): JsonResponse
    {
        $history = $this->levels->userHistory($userId);

        return response()->json(
            $history->map(fn ($h) => [
                'id'           => $h->id,
                'user_id'      => $h->user_id,
                'level_id'     => $h->level_id,
                'assigned_at'  => $h->assigned_at->format('Y-m-d'),
                'points_year'  => $h->points_year,
                'level'        => $h->level ? [
                    'id'                  => $h->level->id,
                    'name'                => $h->level->name,
                    'min_points_per_year' => $h->level->min_points_per_year,
                    'privileges'          => $h->level->privileges?->toArray() ?? [],
                ] : null,
            ])->all()
        );
    }
}