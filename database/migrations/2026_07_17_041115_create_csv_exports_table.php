<?php

declare(strict_types=1);

/**
 * Migration: csv_exports — реестр асинхронных CSV-экспортов
 *
 * Координатор инициирует экспорт → создаётся запись (status=pending)
 * → воркер генерирует файл → status=ready → даём URL на скачивание.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('csv_exports', static function (Blueprint $table): void {
            $table->uuid(column: 'id')->primary();

            $table->foreignId(column: 'user_id')
                ->constrained(table: 'users')
                ->cascadeOnDelete()
                ->comment('Кто запросил экспорт');

            $table->string(column: 'period', length: 7)->comment('YYYY-MM');
            $table->string(column: 'status', length: 16)->default('pending')
                ->comment('pending | processing | ready | failed');
            $table->string(column: 'file_path')->nullable()->comment('Путь в disk=exports');
            $table->unsignedBigInteger(column: 'rows_count')->default(0);
            $table->text(column: 'error')->nullable();

            $table->timestamps();
        });

        DB::statement("ALTER TABLE csv_exports ADD CONSTRAINT csv_exports_status_check
            CHECK (status IN ('pending', 'processing', 'ready', 'failed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('csv_exports');
    }
};
