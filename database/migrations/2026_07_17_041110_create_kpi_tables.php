<?php

declare(strict_types=1);

/**
 * Migration: kpi_categories + kpi_indicators
 *
 * Категории: ПМ, ОЭК, ЭКЛ, КБ.
 * Типы показателей: base | extra | penalty.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_categories', static function (Blueprint $table): void {
            $table->id();
            $table->string('code', 8)->unique()->comment('ПМ | ОЭК | ЭКЛ | КБ');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_indicators', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('kpi_categories')
                ->cascadeOnDelete();

            $table->string('code', 16)->unique()->comment('ПМ1, ДПМ1, ШОЭК и т.д.');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 8)->comment('%, шт, чел');

            $table->string('indicator_type', 8)->comment('base | extra | penalty');

            // Для базовых (base)
            $table->float('base_value')->nullable()->comment('Пороговое значение');
            $table->integer('base_weight')->nullable()->comment('Вес (если факт >= base_value)');

            // Для дополнительных (extra)
            $table->integer('extra_weight')->nullable()->comment('Множитель баллов');

            // Для штрафных (penalty)
            $table->integer('penalty_weight')->nullable()->comment('Отрицательный множитель');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE kpi_indicators ADD CONSTRAINT kpi_indicators_type_check
            CHECK (indicator_type IN ('base', 'extra', 'penalty'))");

        DB::statement('CREATE INDEX idx_kpi_indicators_category ON kpi_indicators (category_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
        Schema::dropIfExists('kpi_categories');
    }
};