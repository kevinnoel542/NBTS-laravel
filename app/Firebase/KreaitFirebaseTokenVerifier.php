<?php

namespace App\Firebase;

use App\Exceptions\InvalidFirebaseToken;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;

final readonly class KreaitFirebaseTokenVerifier implements FirebaseTokenVerifier
{
    public function __construct(private Auth $auth) {}

    public function verify(string $idToken): VerifiedFirebaseIdentity
    {
        try {
            $claims = $this->auth->verifyIdToken(
                $this->nonEmptyToken($idToken),
                checkIfRevoked: true,
            )->claims();
        } catch (FailedToVerifyToken|RevokedIdToken) {
            throw new InvalidFirebaseToken;
        }

        $uid = $claims->get('sub');

        if (! is_string($uid) || $uid === '' || mb_strlen($uid) > 128) {
            throw new InvalidFirebaseToken;
        }

        $firebase = $claims->get('firebase', []);
        $provider = is_array($firebase) && is_string($firebase['sign_in_provider'] ?? null)
            ? $firebase['sign_in_provider']
            : 'firebase';

        return new VerifiedFirebaseIdentity(
            uid: $uid,
            email: $this->nullableString($claims->get('email')),
            emailVerified: $claims->get('email_verified') === true,
            name: $this->nullableString($claims->get('name')),
            photoUrl: $this->nullableString($claims->get('picture')),
            provider: mb_substr($provider, 0, 255),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    /** @return non-empty-string */
    private function nonEmptyToken(string $idToken): string
    {
        if ($idToken === '') {
            throw new InvalidFirebaseToken;
        }

        return $idToken;
    }
}
