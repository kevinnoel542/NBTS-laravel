<?php

namespace App\Firebase;

final readonly class VerifiedFirebaseIdentity
{
    public function __construct(
        public string $uid,
        public ?string $email,
        public bool $emailVerified,
        public ?string $name,
        public ?string $photoUrl,
        public string $provider,
    ) {}
}
