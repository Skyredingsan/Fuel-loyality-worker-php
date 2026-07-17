<?php

declare(strict_types=1);

namespace FuelPoints\File\Domain\Repositories;

use Illuminate\Http\UploadedFile;

interface FileRepositoryInterface
{
    /**
     * Store uploaded file under uploads/{type}/ folder.
     * Returns the public URL (relative path).
     */
    public function store(
        UploadedFile $file,
        string $type,
        ?string $entityId = null,
    ): string;

    public function delete(string $type, string $filename): bool;

    public function fullPath(string $type, string $filename): string;

    public function exists(string $type, string $filename): bool;
}