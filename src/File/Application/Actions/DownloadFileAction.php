<?php

declare(strict_types=1);

namespace FuelPoints\File\Application\Actions;

use FuelPoints\File\Domain\Repositories\FileRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DownloadFileAction
{
    public function __construct(
        private FileRepositoryInterface $files,
    ) {}

    public function execute(string $type, string $filename): StreamedResponse
    {
        if (!$this->files->exists($type, $filename)) {
            throw new \DomainException("File not found: {$type}/{$filename}");
        }

        $mimeType = mime_content_type($this->files->fullPath($type, $filename)) ?: 'application/octet-stream';

        return response()->streamDownload(function () use ($type, $filename): void {
            echo file_get_contents($this->files->fullPath($type, $filename));
        }, $filename, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}