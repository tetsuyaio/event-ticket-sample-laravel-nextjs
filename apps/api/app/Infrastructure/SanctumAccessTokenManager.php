<?php

namespace App\Infrastructure;

use App\Contracts\AccessTokenManagerInterface;
use App\Models\User;

final class SanctumAccessTokenManager implements AccessTokenManagerInterface
{
    public function issue(User $user, string $name): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    public function revokeCurrent(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
