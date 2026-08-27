<?php

use App\Enums\UserRole;
use App\Models\User;

it('creates a user with a USER role and returns a token on signup', function (): void {
    $response = $this->postJson('/api/auth/signup', [
        'name' => 'Sample User',
        'email' => 'USER@EXAMPLE.COM',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'ADMIN',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'user@example.com')
        ->assertJsonPath('data.role', 'USER')
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role'], 'token']);
    $this->assertDatabaseHas('users', ['email' => 'user@example.com', 'role' => UserRole::User->value]);
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('returns 409 when the email already exists', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/auth/signup', [
        'name' => 'Another User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertConflict()->assertJson([
        'message' => 'Email already exists',
        'code' => 'EMAIL_ALREADY_EXISTS',
        'errors' => null,
    ]);
});

it('returns 422 with the common error shape for invalid signup data', function (): void {
    $this->postJson('/api/auth/signup', [])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['message', 'code', 'errors' => ['name', 'email', 'password']]);
});

it('returns a token for valid credentials and rejects invalid credentials', function (): void {
    User::factory()->create(['email' => 'login@example.com', 'password' => 'password123']);

    $this->postJson('/api/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ])->assertOk()->assertJsonStructure(['data', 'token']);

    $this->postJson('/api/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ])->assertUnauthorized()->assertJsonPath('code', 'INVALID_CREDENTIALS');
});

it('returns the current user and revokes only the current token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();
    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHORIZED');
});
