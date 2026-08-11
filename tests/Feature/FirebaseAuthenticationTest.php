<?php

use App\Exceptions\InvalidFirebaseToken;
use App\Firebase\FirebaseTokenVerifier;
use App\Firebase\VerifiedFirebaseIdentity;
use App\Models\AuditLog;
use App\Models\User;
use App\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function bindFirebaseIdentity(VerifiedFirebaseIdentity $identity): void
{
    app()->instance(FirebaseTokenVerifier::class, new readonly class($identity) implements FirebaseTokenVerifier
    {
        public function __construct(private VerifiedFirebaseIdentity $identity) {}

        public function verify(string $idToken): VerifiedFirebaseIdentity
        {
            return $this->identity;
        }
    });
}

function verifiedFirebaseIdentity(
    string $uid = 'firebase-uid-123',
    ?string $email = 'donor@example.test',
    bool $emailVerified = true,
): VerifiedFirebaseIdentity {
    return new VerifiedFirebaseIdentity(
        uid: $uid,
        email: $email,
        emailVerified: $emailVerified,
        name: 'Asha Donor',
        photoUrl: 'https://example.test/asha.jpg',
        provider: 'google.com',
    );
}

test('a verified Firebase identity creates a donor and a scoped device token', function () {
    bindFirebaseIdentity(verifiedFirebaseIdentity(email: 'ASHA@EXAMPLE.TEST'));

    $response = $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'verified-firebase-token',
        'device_name' => 'Asha Pixel 9',
    ]);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->where('token_type', 'Bearer')
            ->where('user.name', 'Asha Donor')
            ->where('user.email', 'asha@example.test')
            ->where('user.locale', 'en')
            ->where('user.total_volume_ml', 0)
            ->where('user.roles.0', RoleName::Donor->value)
            ->where('user.donor_profile.blood_group_status', 'unknown')
            ->hasAll(['token', 'expires_at', 'user.donor_profile.donor_id'])
            ->etc());

    $user = User::query()->where('firebase_uid', 'firebase-uid-123')->firstOrFail();
    $accessToken = $user->tokens()->sole();

    expect($user->hasRole(RoleName::Donor->value))->toBeTrue()
        ->and($user->donorProfile)->not->toBeNull()
        ->and($accessToken->name)->toBe('Asha Pixel 9')
        ->and($accessToken->can('donor:read'))->toBeTrue()
        ->and($accessToken->can('donor:write'))->toBeTrue()
        ->and($accessToken->expires_at?->isBetween(now()->addDays(29), now()->addDays(31)))->toBeTrue()
        ->and(AuditLog::query()->where('action', 'mobile.firebase_account_created')->count())->toBe(1);
});

test('a verified email safely links an existing donor without replacing donor data', function () {
    $donor = User::factory()->donor()->create([
        'name' => 'Existing Donor Name',
        'email' => 'donor@example.test',
        'email_verified_at' => null,
    ]);
    bindFirebaseIdentity(verifiedFirebaseIdentity());

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'verified-firebase-token',
        'device_name' => 'Donor Android',
    ])->assertOk();

    $donor->refresh();

    expect($donor->name)->toBe('Existing Donor Name')
        ->and($donor->firebase_uid)->toBe('firebase-uid-123')
        ->and($donor->email_verified_at)->not->toBeNull()
        ->and($donor->donorProfile)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'mobile.firebase_authenticated')->count())->toBe(1);
});

test('an already linked Firebase identity does not depend on repeated email claims', function () {
    $donor = User::factory()->donor()->create([
        'firebase_uid' => 'firebase-uid-123',
    ]);
    bindFirebaseIdentity(verifiedFirebaseIdentity(email: null, emailVerified: false));

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'verified-firebase-token',
        'device_name' => 'Linked Device',
    ])->assertOk()->assertJsonPath('user.id', $donor->id);
});

test('new and unlinked accounts require a verified Firebase email', function () {
    bindFirebaseIdentity(verifiedFirebaseIdentity(emailVerified: false));

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'unverified-email-token',
        'device_name' => 'Unverified Device',
    ])->assertUnprocessable()->assertJsonValidationErrors('firebase_id_token');

    expect(User::query()->where('firebase_uid', 'firebase-uid-123')->exists())->toBeFalse();
});

test('donor mobile authentication cannot auto-link staff or inactive accounts', function () {
    $staff = User::factory()->create(['email' => 'staff@example.test']);
    bindFirebaseIdentity(verifiedFirebaseIdentity(email: 'staff@example.test'));

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'staff-token',
        'device_name' => 'Staff Device',
    ])->assertForbidden();

    $inactiveDonor = User::factory()->donor()->inactive()->create(['email' => 'inactive@example.test']);
    bindFirebaseIdentity(verifiedFirebaseIdentity(uid: 'inactive-uid', email: 'inactive@example.test'));

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'inactive-token',
        'device_name' => 'Inactive Device',
    ])->assertForbidden();

    expect($staff->refresh()->firebase_uid)->toBeNull()
        ->and($inactiveDonor->refresh()->firebase_uid)->toBeNull()
        ->and(PersonalAccessToken::query()->count())->toBe(0);
});

test('conflicting Firebase UID and verified email mappings are rejected', function () {
    User::factory()->donor()->create([
        'email' => 'first@example.test',
        'firebase_uid' => 'firebase-uid-123',
    ]);
    User::factory()->donor()->create(['email' => 'second@example.test']);
    bindFirebaseIdentity(verifiedFirebaseIdentity(email: 'second@example.test'));

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'conflicting-token',
        'device_name' => 'Conflicting Device',
    ])->assertUnprocessable()->assertJsonValidationErrors('firebase_id_token');

    expect(PersonalAccessToken::query()->count())->toBe(0);
});

test('invalid Firebase tokens return a localized authentication error', function () {
    app()->instance(FirebaseTokenVerifier::class, new class implements FirebaseTokenVerifier
    {
        public function verify(string $idToken): VerifiedFirebaseIdentity
        {
            throw new InvalidFirebaseToken;
        }
    });

    $this->postJson(route('api.v1.auth.firebase'), [
        'firebase_id_token' => 'invalid-token',
        'device_name' => 'Unknown Device',
    ], ['X-Locale' => 'sw'])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Uthibitishaji wa Firebase umeshindikana.');
});

test('a device login replaces its prior token and logout revokes the current token', function () {
    bindFirebaseIdentity(verifiedFirebaseIdentity());
    $payload = [
        'firebase_id_token' => 'verified-firebase-token',
        'device_name' => 'Asha Phone',
    ];

    $this->postJson(route('api.v1.auth.firebase'), $payload)->assertOk();
    $token = $this->postJson(route('api.v1.auth.firebase'), $payload)
        ->assertOk()
        ->json('token');

    expect($token)->toBeString()
        ->and(PersonalAccessToken::query()->where('name', 'Asha Phone')->count())->toBe(1);

    $this->withToken($token)->getJson(route('api.v1.me'))
        ->assertOk()
        ->assertJsonPath('data.email', 'donor@example.test');

    $this->withToken($token)->postJson(route('api.v1.logout'))->assertNoContent();

    expect(PersonalAccessToken::query()->where('name', 'Asha Phone')->exists())->toBeFalse();

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson(route('api.v1.me'))->assertUnauthorized();
});

test('inactive accounts are denied on protected mobile routes', function () {
    $donor = User::factory()->donor()->inactive()->create();
    $token = $donor->createToken('Inactive Phone', ['donor:read'])->plainTextToken;

    $this->withToken($token)->getJson(route('api.v1.me'), ['X-Locale' => 'sw'])
        ->assertForbidden()
        ->assertJsonPath(
            'message',
            'Akaunti hii haifanyi kazi. Wasiliana na msimamizi wa NBTS kwa msaada.',
        );
});
