<?php

declare(strict_types=1);

/**
 * Migration: levels + user_level_history
 *
 * Уровни (из Go-версии):
 *   - Специалист Трассы (0 баллов)
 *   - Тактик Магистрали (4321 баллов/год)
 *   - Стратег Гран-при (4321 баллов/год) — в оригинале тот же порог, оставляем как есть
 *
 * Privileges хранятся в JSONB — гибкая структура бонусов.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('min_points_per_year')->default(0);
            $table->jsonb('privileges')->nullable()->comment('JSON: bonus, prize, ...');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_levels_min_points
            ON levels (min_points_per_year)');

        Schema::create('user_level_history', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('level_id')
                ->constrained('levels')
                ->cascadeOnDelete();

            $table->date('assigned_at')->comment('Дата присвоения');
            $table->integer('points_year')->comment('Сумма баллов за год на момент присвоения');

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_user_level_history_user
            ON user_level_history (user_id, assigned_at DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_level_history');
        Schema::dropIfExists('levels');
    }
};