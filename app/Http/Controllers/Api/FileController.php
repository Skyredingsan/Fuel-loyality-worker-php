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
    ) {}

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

        $file = $request->file('file');
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return $this->error('No file uploaded', 400);
        }

        try {
            $result = $this->upload->execute(
                $file,
                $request->input('type'),
                $request->input('entity_id'),
            );

            return response()->json($result, 201);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Скачивание файла.
     */
    public function download(string $type, string $filename): StreamedResponse|JsonResponse
    {
        try {
            return $this->download->execute($type, $filename);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Удаление файла.
     */
    public function destroy(string $type, string $filename): JsonResponse
    {
        try {
            $this->delete->execute($type, $filename);

            return response()->json(null, 204);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 404);
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