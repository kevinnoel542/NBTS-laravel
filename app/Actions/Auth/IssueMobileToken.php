<?php

namespace App\Actions\Auth;

use App\Auth\IssuedMobileToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class IssueMobileToken
{
    public function handle(User $user, string $deviceName): IssuedMobileToken
    {
        return DB::transaction(function () use ($user, $deviceName): IssuedMobileToken {
            $expiresAt = now()->toImmutable()->addDays(
                (int) config('nbts.mobile_token_expiration_days', 30),
            );

            $user->tokens()->where('name', $deviceName)->delete();

            $token = $user->createToken(
                name: $deviceName,
                abilities: ['donor:read', 'donor:write'],
                expiresAt: $expiresAt,
            );

            return new IssuedMobileToken(
                plainTextToken: $token->plainTextToken,
                expiresAt: $expiresAt,
            );
        });
    }
}
