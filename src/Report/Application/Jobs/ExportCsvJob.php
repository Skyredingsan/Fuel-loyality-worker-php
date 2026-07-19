<?php

declare(strict_types=1);

namespace FuelPoints\Report\Application\Jobs;

use FuelPoints\Report\Application\Services\CsvExportGenerator;
use FuelPoints\Report\Domain\Models\CsvExport;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Job: асинхронная генерация CSV-экспорта.
 *
 * Flow:
 *   1. Coordinator инициирует экспорт → создаётся CsvExport (status=pending)
 *   2. Job диспатчится в очередь
 *   3. Worker подхватывает → ставит status=processing
 *   4. Генерирует файл → status=ready, file_path
 *   5. Coordinator поллит GET /api/reports/exports/{id}
 *
 * При ошибке → status=failed, error=...
 */
final class ExportCsvJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;
    public int $timeout = 120;

    public function __construct(
        public readonly string $exportId,
    ) {}

    public function handle(CsvExportGenerator $generator): void
    {
        Log::info("Starting CSV export #{$this->exportId}");

        $export = CsvExport::find($this->exportId);
        if ($export === null) {
            Log::error("Export record not found: {$this->exportId}");
            return;
        }

        try {
            // Помечаем processing
            $export->update(['status' => 'processing']);

            $period = Period::fromString($export->period);
            $path = $generator->generateForPeriod($period, $export);

            $rowsCount = $this->countRows($path);

            $export->update([
                'status'     => 'ready',
                'file_path'  => $path,
                'rows_count' => $rowsCount,
            ]);

            Log::info("CSV export #{$this->exportId} ready: {$rowsCount} rows");

        } catch (Throwable $e) {
            Log::error("CSV export #{$this->exportId} failed: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            $export->update([
                'status' => 'failed',
                'error'  => mb_substr($e->getMessage(), 0, 1000),
            ]);
        }
    }

    private function countRows(string $path): int
    {
        $content = Storage::disk('exports')->get($path);
        if ($content === null) {
            return 0;
        }

        return max(0, substr_count($content, "\n") - 1);
    }
}