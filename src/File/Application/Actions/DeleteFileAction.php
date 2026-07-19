<?php

declare(strict_types=1);

namespace FuelPoints\File\Application\Actions;

use FuelPoints\File\Domain\Repositories\FileRepositoryInterface;

final readonly class DeleteFileAction
{
    public function __construct(
        private FileRepositoryInterface $files,
    ) {}

    public function execute(string $type, string $filename): bool
    {
        return $this->files->delete($type, $filename);
    }
}