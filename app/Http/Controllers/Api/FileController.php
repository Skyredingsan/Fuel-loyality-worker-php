<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use FuelPoints\File\Application\Actions\DeleteFileAction;
use FuelPoints\File\Application\Actions\DownloadFileAction;
use FuelPoints\File\Application\Actions\UploadFileAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @tags Файлы
 */
final class FileController extends Controller
{
    public function __construct(
        private readonly UploadFileAction $upload,
        private readonly DeleteFileAction $delete,
        private readonly DownloadFileAction $download,
    ) {
    }

    /**
     * Загрузка файла (multipart/form-data).
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'      => ['required', 'file', 'max:10240'],
            'type'      => ['required', 'string', 'in:indicator_result,general,user_avatar'],
            'entity_id' => ['nullable', 'string', 'max:100'],
        ]);

        $file = $request->file(key: 'file');
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return $this->error(message: 'No file uploaded', status: 400);
        }

        try {
            $result = $this->upload->execute(
                file: $file,
                type: $request->input(key: 'type'),
                entityId: $request->input(key: 'entity_id'),
            );

            return response()->json($result, 201);
        } catch (\DomainException $e) {
            return $this->error(message: $e->getMessage(), status: 400);
        }
    }

    /**
     * Скачивание файла.
     */
    public function download(string $type, string $filename): StreamedResponse|JsonResponse
    {
        try {
            return $this->download->execute(type: $type, filename: $filename);
        } catch (\DomainException $e) {
            return $this->error(message: $e->getMessage(), status: 404);
        }
    }

    /**
     * Удаление файла.
     */
    public function destroy(string $type, string $filename): JsonResponse
    {
        try {
            $this->delete->execute(type: $type, filename: $filename);

            return response()->json(null, 204);
        } catch (\DomainException $e) {
            return $this->error(message: $e->getMessage(), status: 404);
        }
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
