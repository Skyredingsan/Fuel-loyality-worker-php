<?php

declare(strict_types=1);

use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(classAndTraits1: RefreshDatabase::class);

beforeEach(closure: function (): void {
    ['token' => $this->token] = authUser(UserRole::EXPERT);
    Storage::fake(disk: 'uploads');
});

it(description: 'uploads a PDF file successfully', closure: function (): void {
    $file = UploadedFile::fake()->createWithContent(
        name: 'document.pdf',
        content: str_repeat(string: 'PDF content ', times: 100)
    );

    $response = $this->withHeaders(jwtHeader($this->token))
        ->post('/api/upload', [
            'file'      => $file,
            'type'      => 'indicator_result',
            'entity_id' => '123',
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['url', 'filename', 'size', 'mime_type']);

    $url = $response->json('url');
    expect(value: $url)->toStartWith('/uploads/indicator_result/');
});

it(description: 'rejects disallowed file extension', closure: function (): void {
    $file = UploadedFile::fake()->createWithContent(
        name: 'malicious.exe',
        content: str_repeat(string: 'fake exe content', times: 50)
    );

    $this->withHeaders(jwtHeader($this->token))
        ->post('/api/upload', [
            'file' => $file,
            'type' => 'general',
        ])
        ->assertStatus(400);
});

it(description: 'rejects upload larger than 10MB', closure: function (): void {
    // Создаём файл 12 МБ
    $file = UploadedFile::fake()->create(name: 'big.pdf', kilobytes: 12000, mimeType: 'application/pdf');

    $this->withHeaders(jwtHeader($this->token))
        ->post('/api/upload', [
            'file' => $file,
            'type' => 'general',
        ])
        ->assertStatus(422);
});

it(description: 'prevents TM from uploading', closure: function (): void {
    ['token' => $tmToken] = authUser(UserRole::TM);

    $file = UploadedFile::fake()->create(name: 'doc.pdf', kilobytes: 1000, mimeType: 'application/pdf');

    $this->withHeaders(jwtHeader($tmToken))
        ->post('/api/upload', [
            'file' => $file,
            'type' => 'general',
        ])
        ->assertForbidden();
});
