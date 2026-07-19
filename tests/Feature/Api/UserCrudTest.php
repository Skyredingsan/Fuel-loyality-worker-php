<?php

declare(strict_types=1);

use FuelPoints\User\Domain\Enums\UserRole;
use FuelPoints\User\Domain\Models\User;

beforeEach(function (): void {
    ['user' => $this->coordinator, 'token' => $this->token] = authUser(UserRole::COORDINATOR);
});

it('lists all users as coordinator', function (): void {
    User::factory()->count(3)->tm()->create();

    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(4, 'data'); // 3 TM + 1 coordinator
});

it('filters users by role', function (): void {
    User::factory()->count(2)->tm()->create();
    User::factory()->count(1)->expert()->create();

    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/users?role=tm')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('lists only TMs via /users/tms', function (): void {
    User::factory()->count(3)->tm()->create();
    User::factory()->count(1)->expert()->create();

    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/users/tms')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('creates a new user as coordinator', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/users/register', [
            'email'        => 'new@fuel.ru',
            'password'     => 'secret123',
            'role'         => 'tm',
            'fio'          => 'Иванов Иван Иванович',
            'cluster_name' => 'Север',
            'azs_count'    => 5,
        ])
        ->assertCreated()
        ->assertJsonPath('email', 'new@fuel.ru')
        ->assertJsonPath('role', 'tm');

    $this->assertDatabaseHas('users', ['email' => 'new@fuel.ru']);
});

it('prevents non-coordinator from creating user', function (): void {
    ['token' => $expertToken] = authUser(UserRole::EXPERT);

    $this->withHeaders(jwtHeader($expertToken))
        ->postJson('/api/users/register', [
            'email'    => 'new@fuel.ru',
            'password' => 'secret123',
            'role'     => 'tm',
            'fio'      => 'Иванов',
        ])
        ->assertForbidden();
});

it('cannot delete self', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->deleteJson("/api/users/{$this->coordinator->id}")
        ->assertStatus(400)
        ->assertJsonPath('message', 'Cannot delete yourself');
});

it('updates user info', function (): void {
    $user = User::factory()->tm()->create();

    $this->withHeaders(jwtHeader($this->token))
        ->putJson("/api/users/{$user->id}", [
            'fio'       => 'Новое ФИО',
            'azs_count' => 10,
        ])
        ->assertOk()
        ->assertJsonPath('fio', 'Новое ФИО')
        ->assertJsonPath('azs_count', 10);
});

it('returns 404 for non-existent user', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->getJson('/api/users/99999')
        ->assertNotFound();
});

it('validates store user request', function (): void {
    $this->withHeaders(jwtHeader($this->token))
        ->postJson('/api/users/register', [
            'email'    => 'not-email',
            'password' => '123',
            'role'     => 'invalid',
            'fio'      => '',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password', 'role', 'fio']);
});