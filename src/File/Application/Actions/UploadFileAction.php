<?php

declare(strict_types=1);

namespace FuelPoints\File\Application\Actions;

use FuelPoints\File\Domain\Repositories\FileRepositoryInterface;
use Illuminate\Http\UploadedFile;

/**
 * Action: загрузка файла.
 */
final readonly class UploadFileAction
{
    public function __construct(
        private FileRepositoryInterface $files,
    ) {}

    public function execute(UploadedFile $file, string $type, ?string $entityId = null): array
    {
        $url = $this->files->store($file, $type, $entityId);

        return [
            'url'       => $url,
            'filename'  => $file->getClientOriginalName(),
            'size'      => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }
}