<?php

declare(strict_types=1);

namespace FuelPoints\Level\Domain\Repositories;

use FuelPoints\Level\Domain\Models\Level;
use FuelPoints\Level\Domain\Models\UserLevelHistory;
use Illuminate\Database\Eloquent\Collection;

interface LevelRepositoryInterface
{
    /**
     * @return Collection<int, Level>  sorted by min_points_per_year ASC
     */
    public function all(): Collection;

    public function findById(int $id): ?Level;

    public function findByPoints(int $yearlyPoints): ?Level;

    public function lowest(): Level;

    public function assignToUser(int $userId, int $levelId, int $pointsYear): UserLevelHistory;

    /**
     * Most recent assigned level for user.
     */
    public function currentUserLevel(int $userId): ?Level;

    /**
     * @return Collection<int, UserLevelHistory>
     */
    public function userHistory(int $userId): Collection;
}