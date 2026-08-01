<?php

namespace App\Auth;

use Carbon\CarbonImmutable;

final readonly class IssuedMobileToken
{
    public function __construct(
        public string $plainTextToken,
        public CarbonImmutable $expiresAt,
    ) {}
}
