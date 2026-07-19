<?php

declare(strict_types=1);

namespace FuelPoints\Report\Application\Services;

use FuelPoints\Kpi\Domain\Repositories\KpiRepositoryInterface;
use FuelPoints\Report\Domain\Models\CsvExport;
use FuelPoints\Result\Domain\Repositories\ResultRepositoryInterface;
use FuelPoints\Shared\Domain\ValueObjects\Period;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

/**
 * Pure service: генерация CSV-файла для заданного периода.
 */
final readonly class CsvExportGenerator
{
    public function __construct(
        private ResultRepositoryInterface $results,
        private KpiRepositoryInterface $kpi,
    ) {}

    public function generateForPeriod(Period $period, CsvExport $export): string
    {
        $filename = "export_{$period}_{$export->id}.csv";
        $path = $filename;

        $disk = Storage::disk('exports');

        // Создаём CSV-файл
        $writer = Writer::createFromPath($disk->path($filename), 'w+');

        $writer->setOutputBOM(Writer::BOM_UTF8);
        $writer->setDelimiter(';');

        $writer->insertOne([
            'ID',
            'ТМ (ФИО)',
            'Email',
            'Кластер',
            'Период',
            'Статус',
            'Код показателя',
            'Название показателя',
            'Тип',
            'Факт',
            'Баллы',
            'URL документа',
            'Эксперт (ФИО)',
        ]);

        $monthlyResults = $this->results->monthlyResultsByPeriod($period);

        foreach ($monthlyResults as $monthly) {
            $indicatorResults = $this->results->indicatorResults($monthly->id);

            foreach ($indicatorResults as $result) {
                $writer->insertOne([
                    $monthly->id,
                    $monthly->user?->fio ?? '',
                    $monthly->user?->email ?? '',
                    $monthly->user?->cluster_name ?? '',
                    $monthly->period->format('Y-m'),
                    $monthly->status->label(),
                    $result->indicator?->code ?? '',
                    $result->indicator?->name ?? '',
                    $result->indicator?->indicator_type->label() ?? '',
                    $result->fact_value ?? '',
                    $result->calculated_points,
                    $result->supporting_document_url ?? '',
                    $monthly->expert?->fio ?? '',
                ]);
            }
        }

        return $path;
    }
}