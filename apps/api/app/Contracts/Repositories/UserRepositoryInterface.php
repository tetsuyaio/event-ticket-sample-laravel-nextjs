<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function emailExists(string $email): bool;

    public function findByEmail(string $email): ?User;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): User;
}
