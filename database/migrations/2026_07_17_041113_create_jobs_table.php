<?php

declare(strict_types=1);

/**
 * Migration: jobs + job_batches + failed_jobs (Laravel queue tables)
 *
 * На шаге 11 добавим Redis + worker, эти таблицы нужны как fallback
 * и для failed_jobs (всегда в БД, даже если драйвер очереди — redis).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('jobs', static function (Blueprint $table): void {
            $table->bigIncrements(column: 'id');
            $table->string(column: 'queue')->index();
            $table->longText(column: 'payload');
            $table->unsignedTinyInteger(column: 'attempts');
            $table->unsignedInteger(column: 'reserved_at')->nullable();
            $table->unsignedInteger(column: 'available_at');
            $table->unsignedInteger(column: 'created_at');
        });

        Schema::create('job_batches', static function (Blueprint $table): void {
            $table->string(column: 'id')->primary();
            $table->string(column: 'name');
            $table->integer(column: 'total_jobs');
            $table->integer(column: 'pending_jobs');
            $table->integer(column: 'failed_jobs');
            $table->longText(column: 'failed_job_ids');
            $table->mediumText(column: 'options')->nullable();
            $table->integer(column: 'cancelled_at')->nullable();
            $table->integer(column: 'created_at');
            $table->integer(column: 'finished_at')->nullable();
        });

        Schema::create('failed_jobs', static function (Blueprint $table): void {
            $table->id();
            $table->string(column: 'uuid')->unique();
            $table->text(column: 'connection');
            $table->text(column: 'queue');
            $table->longText(column: 'payload');
            $table->longText(column: 'exception');
            $table->timestamp(column: 'failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
