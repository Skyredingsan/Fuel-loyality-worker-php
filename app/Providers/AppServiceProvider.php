<?php

declare(strict_types=1);

namespace App\Providers;

use FuelPoints\Result\Domain\Services\ScoreCalculator;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ScoreCalculator — pure service, можно как singleton
        $this->app->singleton(ScoreCalculator::class);
    }

    public function boot(): void
    {
        //
    }
}