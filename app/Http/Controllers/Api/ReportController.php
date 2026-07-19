<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ExportRequest;
use FuelPoints\Report\Application\Services\StartCsvExportAction;
use FuelPoints\Report\Domain\Models\CsvExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @tags Отчёты
 */
final class ReportController extends Controller
{
    public function __construct(
        private readonly StartCsvExportAction $startExport,
    ) {}

    /**
     * Запуск CSV-экспорта за период (асинхронно).
     */
    public function export(ExportRequest $request): JsonResponse
    {
        $userId = (int) \Tymon\JWTAuth\Facades\JWTAuth::user()?->id;
        $period = $request->validated()['period'];

        $export = $this->startExport->execute($userId, $period);

        return response()->json([
            'success'    => true,
            'export_id'  => $export->id,
            'status'     => $export->status,
            'period'     => $export->period,
            'check_url'  => "/api/reports/exports/{$export->id}",
        ], 202);
    }

    /**
     * Статус экспорта.
     */
    public function showExport(string $id): JsonResponse
    {
        // Валидация UUID — если не UUID, сразу 404
        if (!\Illuminate\Support\Str::isUuid($id)) {
            return $this->error("Export #{$id} not found", 404);
        }

        $export = CsvExport::find($id);
        if ($export === null) {
            return $this->error("Export #{$id} not found", 404);
        }

        return response()->json([
            'id'           => $export->id,
            'status'       => $export->status,
            'period'       => $export->period,
            'rows_count'   => $export->rows_count,
            'error'        => $export->error,
            'created_at'   => $export->created_at?->toIso8601String(),
            'updated_at'   => $export->updated_at?->toIso8601String(),
            'download_url' => $export->status === 'ready' && $export->file_path
                ? "/api/reports/exports/{$export->id}/download"
                : null,
        ]);
    }

    /**
     * Скачивание готового CSV.
     */
    public function downloadExport(string $id, Request $request): StreamedResponse|JsonResponse
    {
        $export = CsvExport::find($id);
        if ($export === null) {
            return $this->error("Export #{$id} not found", 404);
        }

        if ($export->status !== 'ready' || !$export->file_path) {
            return $this->error("Export not ready yet (status: {$export->status})", 409);
        }

        $user = \Tymon\JWTAuth\Facades\JWTAuth::user();
        if ($export->user_id !== $user?->id && $user?->role !== \FuelPoints\User\Domain\Enums\UserRole::COORDINATOR) {
            return $this->error('Forbidden', 403);
        }

        $filename = basename($export->file_path);

        return Storage::disk('exports')->download($export->file_path, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => $status,
        ], $status);
    }
}