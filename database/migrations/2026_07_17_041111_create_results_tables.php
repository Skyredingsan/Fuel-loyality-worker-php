<?php

declare(strict_types=1);

/**
 * Migration: monthly_results + indicator_results
 *
 * Ежемесячный результат (один на пользователя за период YYYY-MM-01).
 * Подчинённые записи — результаты по конкретным KPI-показателям.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_results', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ТМ, по которому заведён результат');

            $table->foreignId('expert_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Эксперт, вводивший результат');

            $table->date('period')->comment('Первый день месяца YYYY-MM-01');

            $table->string('status', 16)->default('draft')->comment('draft | confirmed');

            $table->timestamps();

            // Один результат на пользователя за период
            $table->unique(['user_id', 'period']);
        });

        DB::statement("ALTER TABLE monthly_results ADD CONSTRAINT monthly_results_status_check
            CHECK (status IN ('draft', 'confirmed'))");

        DB::statement('CREATE INDEX idx_monthly_results_user_period
            ON monthly_results (user_id, period)');

        DB::statement('CREATE INDEX idx_monthly_results_period_status
            ON monthly_results (period, status)');

        Schema::create('indicator_results', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('monthly_result_id')
                ->constrained('monthly_results')
                ->cascadeOnDelete();

            $table->foreignId('indicator_id')
                ->constrained('kpi_indicators')
                ->cascadeOnDelete();

            $table->float('fact_value')->nullable()->comment('Фактическое значение');
            $table->integer('calculated_points')->default(0)->comment('Расчётные баллы');

            $table->string('supporting_document_url')->nullable()->comment('URL подтверждающего документа');

            $table->timestamps();

            $table->unique(['monthly_result_id', 'indicator_id']);
        });

        DB::statement('CREATE INDEX idx_indicator_results_monthly
            ON indicator_results (monthly_result_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_results');
        Schema::dropIfExists('monthly_results');
    }
};