<?php

use App\Exceptions\InvalidFirebaseToken;
use App\Firebase\KreaitFirebaseTokenVerifier;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;
use Mockery\MockInterface;

test('it verifies revocation and maps trusted Firebase claims', function () {
    $token = new Plain(
        headers: new DataSet(['alg' => 'RS256'], 'headers'),
        claims: new DataSet([
            'sub' => 'firebase-uid-123',
            'email' => 'donor@example.test',
            'email_verified' => true,
            'name' => 'Asha Donor',
            'picture' => 'https://example.test/asha.jpg',
            'firebase' => ['sign_in_provider' => 'google.com'],
        ], 'claims'),
        signature: new Signature('signature', 'encoded-signature'),
    );
    $auth = Mockery::mock(Auth::class, function (MockInterface $mock) use ($token): void {
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('valid-token', true)
            ->andReturn($token);
    });

    $identity = (new KreaitFirebaseTokenVerifier($auth))->verify('valid-token');

    expect($identity->uid)->toBe('firebase-uid-123')
        ->and($identity->email)->toBe('donor@example.test')
        ->and($identity->emailVerified)->toBeTrue()
        ->and($identity->name)->toBe('Asha Donor')
        ->and($identity->photoUrl)->toBe('https://example.test/asha.jpg')
        ->and($identity->provider)->toBe('google.com');
});

test('it converts Firebase verification failures to a safe API exception', function () {
    $auth = Mockery::mock(Auth::class, function (MockInterface $mock): void {
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('invalid-token', true)
            ->andThrow(new FailedToVerifyToken('Invalid token'));
    });

    expect(fn () => (new KreaitFirebaseTokenVerifier($auth))->verify('invalid-token'))
        ->toThrow(InvalidFirebaseToken::class);
});
