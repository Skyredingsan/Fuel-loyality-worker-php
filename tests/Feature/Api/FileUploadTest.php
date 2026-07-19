<?php

declare(strict_types=1);

use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    ['token' => $this->token] = authUser(UserRole::EXPERT);
    Storage::fake('uploads');
});

it('uploads a PDF file successfully', function (): void {
    $file = UploadedFile::fake()->createWithContent(
        'document.pdf',
        str_repeat('PDF content ', 100)
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
    expect($url)->toStartWith('/uploads/indicator_result/');
});

it('rejects disallowed file extension', function (): void {
    $file = UploadedFile::fake()->createWithContent(
        'malicious.exe',
        str_repeat('fake exe content', 50)
    );

    $this->withHeaders(jwtHeader($this->token))
        ->post('/api/upload', [
            'file' => $file,
            'type' => 'general',
        ])
        ->assertStatus(400);
});

it('rejects upload larger than 10MB', function (): void {
    // Создаём файл 12 МБ
    $file = UploadedFile::fake()->create('big.pdf', 12000, 'application/pdf');

    $this->withHeaders(jwtHeader($this->token))
        ->post('/api/upload', [
            'file' => $file,
            'type' => 'general',
        ])
        ->assertStatus(422);
});

it('prevents TM from uploading', function (): void {
    ['token' => $tmToken] = authUser(UserRole::TM);

    $file = UploadedFile::fake()->create('doc.pdf', 1000, 'application/pdf');

    $this->withHeaders(jwtHeader($tmToken))
        ->post('/api/upload', [
            'file' => $file,
            'type' => 'general',
        ])
        ->assertForbidden();
});