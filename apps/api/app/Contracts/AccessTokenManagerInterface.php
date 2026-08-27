<?php

namespace App\Contracts;

use App\Models\User;

interface AccessTokenManagerInterface
{
    public function issue(User $user, string $name): string;

    public function revokeCurrent(User $user): void;
}
