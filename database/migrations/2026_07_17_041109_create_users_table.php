<?php

declare(strict_types=1);

/**
 * Migration: users table
 *
 * Роли:
 *   - tm          — Территориальный менеджер (получатель баллов)
 *   - expert      — Эксперт (вводит результаты ТМ)
 *   - coordinator — Координатор (управляет системой, подтверждает)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->id()->comment('PK');

            $table->string('email')->unique()->comment('Логин (корпоративный email)');
            $table->string('password_hash')->comment('bcrypt hash');

            $table->string('role', 16)->comment('tm | expert | coordinator');

            $table->string('fio')->comment('ФИО полностью');
            $table->string('cluster_name')->nullable()->comment('Название кластера/региона');
            $table->integer('azs_count')->default(0)->comment('Кол-во АЗС в управлении');

            $table->timestamps();
            $table->softDeletes();
        });

        // CHECK constraint для роли — Postgres умеет нативно
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check
            CHECK (role IN ('tm', 'expert', 'coordinator'))");

        // Частичный индекс — только неудалённые записи (soft deletes)
        DB::statement('CREATE INDEX idx_users_role ON users (role) WHERE deleted_at IS NULL');

        // GIN-индекс для быстрого поиска по ФИО (pg_trgm)
        DB::statement('CREATE INDEX idx_users_fio_trgm ON users USING gin (fio gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};