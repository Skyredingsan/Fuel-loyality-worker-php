<?php

declare(strict_types=1);

namespace FuelPoints\Level\Infrastructure\Persistence;

use FuelPoints\Level\Domain\Models\Level;
use FuelPoints\Level\Domain\Models\UserLevelHistory;
use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class EloquentLevelRepository implements LevelRepositoryInterface
{
    public function all(): Collection
    {
        return Level::query()
            ->orderBy('min_points_per_year')
            ->get();
    }

    public function findById(int $id): ?Level
    {
        return Level::query()->find($id);
    }

    public function findByPoints(int $yearlyPoints): ?Level
    {
        return Level::query()
            ->where('min_points_per_year', '<=', $yearlyPoints)
            ->orderByDesc('min_points_per_year')
            ->first();
    }

    public function lowest(): Level
    {
        return Level::query()
            ->orderBy('min_points_per_year')
            ->firstOrFail();
    }

    public function assignToUser(int $userId, int $levelId, int $pointsYear): UserLevelHistory
    {
        return UserLevelHistory::create([
            'user_id'     => $userId,
            'level_id'    => $levelId,
            'assigned_at' => now()->toDateString(),
            'points_year' => $pointsYear,
        ]);
    }

    public function currentUserLevel(int $userId): ?Level
    {
        return Level::query()
            ->join('user_level_history', 'levels.id', '=', 'user_level_history.level_id')
            ->where('user_level_history.user_id', $userId)
            ->orderByDesc('user_level_history.assigned_at')
            ->select('levels.*')
            ->first();
    }

    public function userHistory(int $userId): Collection
    {
        return UserLevelHistory::query()
            ->with('level')
            ->where('user_id', $userId)
            ->orderByDesc('assigned_at')
            ->get();
    }
}