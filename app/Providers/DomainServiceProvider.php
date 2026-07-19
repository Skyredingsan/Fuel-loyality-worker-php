<?php

declare(strict_types=1);

namespace App\Providers;

use FuelPoints\File\Domain\Repositories\FileRepositoryInterface;
use FuelPoints\File\Infrastructure\Storage\LocalFileRepository;
use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use FuelPoints\Kpi\Infrastructure\Persistence\EloquentKpiRepository;
use FuelPoints\Level\Domain\Repositories\LevelRepositoryInterface;
use FuelPoints\Level\Infrastructure\Persistence\EloquentLevelRepository;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Result\Infrastructure\Persistence\EloquentResultRepository;
use FuelPoints\User\Domain\Repositories\UserRepositoryInterface;
use FuelPoints\User\Infrastructure\Persistence\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Связывает Domain-интерфейсы с Eloquent-реализациями.
 *
 * В тестах можно подменить: в тестовом ServiceProvider'е
 * переопределить биндинг на InMemory-реализацию.
 */
final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(KpiRepositoryInterface::class, EloquentKpiRepository::class);
        $this->app->bind(ResultRepositoryInterface::class, EloquentResultRepository::class);
        $this->app->bind(LevelRepositoryInterface::class, EloquentLevelRepository::class);
        $this->app->bind(FileRepositoryInterface::class, LocalFileRepository::class);
    }
}