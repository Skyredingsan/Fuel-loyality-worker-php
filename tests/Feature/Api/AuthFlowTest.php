<?php

declare(strict_types=1);

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(closure: function (): void {
    $this->user = User::factory()->create(attributes: [
        'email'         => 'login@test.fuel',
        'password_hash' => Hash::make('password123'),
        'role'          => UserRole::COORDINATOR->value,
    ]);
});

it(description: 'logs in successfully with correct credentials', closure: function (): void {
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

it(description: 'rejects login with wrong password', closure: function (): void {
    $this->postJson('/api/login', [
        'email'    => 'login@test.fuel',
        'password' => 'wrong',
    ])->assertUnauthorized();
});

it(description: 'rejects login with non-existent email', closure: function (): void {
    $this->postJson('/api/login', [
        'email'    => 'nobody@test.fuel',
        'password' => 'whatever',
    ])->assertUnauthorized();
});

it(description: 'validates login request body', closure: function (): void {
    $this->postJson('/api/login', [
        'email'    => 'not-an-email',
        'password' => '',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it(description: 'returns current user via /users/me', closure: function (): void {
    // Используем $this->user из beforeEach, а не authUser()
    $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);

    $this->withHeaders(jwtHeader($token))
        ->getJson('/api/users/me')
        ->assertOk()
        ->assertJsonPath('email', 'login@test.fuel');
});

it(description: 'rejects request without token', closure: function (): void {
    $this->getJson('/api/users/me')->assertUnauthorized();
});

it(description: 'rejects request with invalid token', closure: function (): void {
    $this->withHeader('Authorization', 'Bearer invalid.token.here')
        ->getJson('/api/users/me')
        ->assertUnauthorized();
});

it(description: 'logs out successfully', closure: function (): void {
    ['token' => $token] = authUser();

    $this->withHeaders(jwtHeader($token))
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('success', true);
});
