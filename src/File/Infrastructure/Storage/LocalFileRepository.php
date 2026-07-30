<?php

declare(strict_types=1);

namespace FuelPoints\File\Infrastructure\Storage;

use FuelPoints\File\Domain\Repositories\FileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Хранение файлов в Laravel Filesystem (disk=uploads).
 */
final class LocalFileRepository implements FileRepositoryInterface
{
    private const array ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    public function store(
        UploadedFile $file,
        string $type,
        ?string $entityId = null,
    ): string {
        $this->ensureTypeSafe(type: $type);

        $ext = strtolower(string: $file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        if (!in_array(needle: $ext, haystack: self::ALLOWED_EXTENSIONS, strict: true)) {
            throw new \DomainException(message: 'File type not allowed: '.$ext);
        }

        $entityPart = $entityId !== null
            ? '_'.Str::slug(title: (string) $entityId)
            : '';
        $filename = sprintf(
            '%d_%s%s.%s',
            now()->getTimestampMs(),
            Str::slug(title: $type),
            $entityPart,
            $ext,
        );

        $path = $file->storeAs(path: $type, name: $filename, options: 'uploads');

        return "/uploads/{$path}";
    }

    public function delete(string $type, string $filename): bool
    {
        $this->ensureTypeSafe(type: $type);
        $this->ensureFilenameSafe(filename: $filename);

        return Storage::disk('uploads')->delete("{$type}/{$filename}");
    }

    public function fullPath(string $type, string $filename): string
    {
        $this->ensureTypeSafe(type: $type);
        $this->ensureFilenameSafe(filename: $filename);

        return Storage::disk('uploads')->path("{$type}/{$filename}");
    }

    public function exists(string $type, string $filename): bool
    {
        $this->ensureTypeSafe(type: $type);
        $this->ensureFilenameSafe(filename: $filename);

        return Storage::disk('uploads')->exists("{$type}/{$filename}");
    }

    /**
     * Защита от path traversal: только a-z, 0-9, _-
     */
    private function ensureTypeSafe(string $type): void
    {
        if (!preg_match(pattern: '/^[a-z0-9_-]+$/i', subject: $type)) {
            throw new \DomainException(message: "Invalid type: {$type}");
        }
    }

    private function ensureFilenameSafe(string $filename): void
    {
        if (str_contains(haystack: $filename, needle: '/') || str_contains(haystack: $filename, needle: '..')) {
            throw new \DomainException(message: "Invalid filename: {$filename}");
        }
    }
}
