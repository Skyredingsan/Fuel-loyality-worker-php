<?php

declare(strict_types=1);

namespace FuelPoints\Report\Application\Services;

use FuelPoints\Report\Application\Jobs\ExportCsvJob;
use FuelPoints\Report\Domain\Models\CsvExport;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Support\Facades\DB;

/**
 * Action: запуск экспорта.
 *
 * Создаёт запись в csv_exports + диспатчит ExportCsvJob в очередь.
 * Возвращает DTO для ответа (id + status=pending).
 */
final readonly class StartCsvExportAction
{
    public function __construct(
        private CsvExportGenerator $generator,
    ) {}

    public function execute(int $userId, string $period): CsvExport
    {
        // Валидация периода — просто пытаемся создать VO
        Period::fromString($period);

        return DB::transaction(function () use ($userId, $period): CsvExport {
            $export = CsvExport::create([
                'user_id' => $userId,
                'period'  => $period,
                'status'  => 'pending',
            ]);

            // Диспатчим Job в очередь (sync для dev, redis для prod)
            ExportCsvJob::dispatch($export->id);

            return $export;
        });
    }
}