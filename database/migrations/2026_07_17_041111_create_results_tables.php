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

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('monthly_results', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId(column: 'user_id')
                ->constrained(table: 'users')
                ->cascadeOnDelete()
                ->comment('ТМ, по которому заведён результат');

            $table->foreignId(column: 'expert_id')
                ->nullable()
                ->constrained(table: 'users')
                ->nullOnDelete()
                ->comment('Эксперт, вводивший результат');

            $table->date(column: 'period')->comment('Первый день месяца YYYY-MM-01');

            $table->string(column: 'status', length: 16)->default('draft')->comment('draft | confirmed');

            $table->timestamps();

            // Один результат на пользователя за период
            $table->unique(columns: ['user_id', 'period']);
        });

        DB::statement("ALTER TABLE monthly_results ADD CONSTRAINT monthly_results_status_check
            CHECK (status IN ('draft', 'confirmed'))");

        DB::statement('CREATE INDEX idx_monthly_results_user_period
            ON monthly_results (user_id, period)');

        DB::statement('CREATE INDEX idx_monthly_results_period_status
            ON monthly_results (period, status)');

        Schema::create('indicator_results', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId(column: 'monthly_result_id')
                ->constrained(table: 'monthly_results')
                ->cascadeOnDelete();

            $table->foreignId(column: 'indicator_id')
                ->constrained(table: 'kpi_indicators')
                ->cascadeOnDelete();

            $table->float(column: 'fact_value')->nullable()->comment('Фактическое значение');
            $table->integer(column: 'calculated_points')->default(0)->comment('Расчётные баллы');

            $table->string(column: 'supporting_document_url')->nullable()->comment('URL подтверждающего документа');

            $table->timestamps();

            $table->unique(columns: ['monthly_result_id', 'indicator_id']);
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
