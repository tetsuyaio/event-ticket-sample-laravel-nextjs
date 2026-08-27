<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function emailExists(string $email): bool
    {
        return User::query()->where('email', $email)->exists();
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }
}
