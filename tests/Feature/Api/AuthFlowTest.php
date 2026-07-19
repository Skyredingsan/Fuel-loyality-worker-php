<?php

declare(strict_types=1);

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email'         => 'login@test.fuel',
        'password_hash' => Hash::make('password123'),
        'role'          => UserRole::COORDINATOR->value,
    ]);
});

it('logs in successfully with correct credentials', function (): void {
    $response = $this->postJson('/api/login', [
        'email'    => 'login@test.fuel',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'token_type',
            'expires_in',
            'user' => ['id', 'email', 'role', 'fio'],
        ]);
});

it('rejects login with wrong password', function (): void {
    $this->postJson('/api/login', [
        'email'    => 'login@test.fuel',
        'password' => 'wrong',
    ])->assertUnauthorized();
});

it('rejects login with non-existent email', function (): void {
    $this->postJson('/api/login', [
        'email'    => 'nobody@test.fuel',
        'password' => 'whatever',
    ])->assertUnauthorized();
});

it('validates login request body', function (): void {
    $this->postJson('/api/login', [
        'email'    => 'not-an-email',
        'password' => '',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('returns current user via /users/me', function (): void {
    // Используем $this->user из beforeEach, а не authUser()
    $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);

    $this->withHeaders(jwtHeader($token))
        ->getJson('/api/users/me')
        ->assertOk()
        ->assertJsonPath('email', 'login@test.fuel');
});

it('rejects request without token', function (): void {
    $this->getJson('/api/users/me')->assertUnauthorized();
});

it('rejects request with invalid token', function (): void {
    $this->withHeader('Authorization', 'Bearer invalid.token.here')
        ->getJson('/api/users/me')
        ->assertUnauthorized();
});

it('logs out successfully', function (): void {
    ['token' => $token] = authUser();

    $this->withHeaders(jwtHeader($token))
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('success', true);
});