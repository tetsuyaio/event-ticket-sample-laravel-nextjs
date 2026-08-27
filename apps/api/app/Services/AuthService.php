<?php

namespace App\Services;

use App\Contracts\AccessTokenManagerInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Data\AuthResult;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AccessTokenManagerInterface $tokens,
        private readonly Hasher $hasher,
    ) {}

    /** @param array{name: string, email: string, password: string} $data */
    public function signup(array $data): AuthResult
    {
        if ($this->users->emailExists($data['email'])) {
            throw new ApiException('Email already exists', 'EMAIL_ALREADY_EXISTS', 409);
        }

        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::User,
        ]);

        return new AuthResult($user, $this->tokens->issue($user, 'bff'));
    }

    /** @param array{email: string, password: string} $data */
    public function login(array $data): AuthResult
    {
        $user = $this->users->findByEmail($data['email']);

        if ($user === null || ! $this->hasher->check($data['password'], $user->password)) {
            throw new ApiException('Invalid credentials', 'INVALID_CREDENTIALS', 401);
        }

        return new AuthResult($user, $this->tokens->issue($user, 'bff'));
    }

    public function logout(User $user): void
    {
        $this->tokens->revokeCurrent($user);
    }
}
