<?php

declare(strict_types=1);

namespace FuelPoints\File\Infrastructure\Storage;

use FuelPoints\File\Domain\Repositories\FileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Хранение файлов в Laravel Filesystem (disk=uploads).
 *
 * При 30 юзерах объём документов минимален — локальный диск ок.
 * Если позже захотим S3 — просто поменяем конфиг filesystems.disks.uploads.
 */
final class LocalFileRepository implements FileRepositoryInterface
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    public function store(
        UploadedFile $file,
        string $type,
        ?string $entityId = null,
    ): string {
        $this->ensureTypeSafe($type);

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new \DomainException('File type not allowed: '.$ext);
        }

        $entityPart = $entityId !== null
            ? '_'.Str::slug((string) $entityId)
            : '';
        $filename = sprintf(
            '%d_%s%s.%s',
            now()->getTimestampMs(),
            Str::slug($type),
            $entityPart,
            $ext,
        );

        $path = $file->storeAs($type, $filename, 'uploads');

        return "/uploads/{$path}";
    }

    public function delete(string $type, string $filename): bool
    {
        $this->ensureTypeSafe($type);
        $this->ensureFilenameSafe($filename);

        return Storage::disk('uploads')->delete("{$type}/{$filename}");
    }

    public function fullPath(string $type, string $filename): string
    {
        $this->ensureTypeSafe($type);
        $this->ensureFilenameSafe($filename);

        return Storage::disk('uploads')->path("{$type}/{$filename}");
    }

    public function exists(string $type, string $filename): bool
    {
        $this->ensureTypeSafe($type);
        $this->ensureFilenameSafe($filename);

        return Storage::disk('uploads')->exists("{$type}/{$filename}");
    }

    /**
     * Защита от path traversal: только a-z, 0-9, _-
     */
    private function ensureTypeSafe(string $type): void
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $type)) {
            throw new \DomainException("Invalid type: {$type}");
        }
    }

    private function ensureFilenameSafe(string $filename): void
    {
        if (str_contains($filename, '/') || str_contains($filename, '..')) {
            throw new \DomainException("Invalid filename: {$filename}");
        }
    }
}