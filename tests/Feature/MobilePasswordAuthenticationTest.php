<?php

use App\Models\AuditLog;
use App\Models\User;
use App\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('a donor can register with the legacy Flutter payload and receives a scoped token', function () {
    $response = $this->postJson(route('api.v1.auth.register'), [
        'name' => 'Neema Mushi',
        'email' => 'NEEMA@EXAMPLE.TEST',
        'phone' => '+255712345678',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
        'blood_group' => 'O+',
        'gender' => 'female',
        'region' => 'Arusha',
        'date_of_birth' => '1995-04-12',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'neema@example.test')
        ->assertJsonPath('user.phone', '+255712345678')
        ->assertJsonPath('user.blood_group', 'O+')
        ->assertJsonPath('user.profile_complete', true)
        ->assertJsonPath('user.total_volume_ml', 0)
        ->assertJsonPath('user.roles.0', RoleName::Donor->value)
        ->assertJsonStructure(['token', 'expires_at', 'user' => ['donor_id']]);

    $user = User::query()->where('phone', '+255712345678')->firstOrFail();
    $token = $user->tokens()->sole();

    expect($user->email)->toBe('neema@example.test')
        ->and(Hash::check('StrongPassword123!', $user->password))->toBeTrue()
        ->and($user->hasRole(RoleName::Donor->value))->toBeTrue()
        ->and($user->donorProfile?->blood_group_status->value)->toBe('user_selected')
        ->and($token->name)->toBe('NBTS Mobile')
        ->and($token->can('donor:read'))->toBeTrue()
        ->and($token->can('donor:write'))->toBeTrue()
        ->and(AuditLog::query()->where('action', 'mobile.password_account_created')->count())->toBe(1);
});

test('registration supports donors without an email while enforcing unique phone identity', function () {
    $payload = [
        'name' => 'Baraka Donor',
        'phone' => '+255700000001',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
        'blood_group' => 'A-',
        'gender' => 'male',
        'region' => 'Mwanza',
        'date_of_birth' => '1990-02-03',
    ];

    $this->postJson(route('api.v1.auth.register'), $payload)
        ->assertCreated()
        ->assertJsonPath('user.email', null);

    $this->postJson(route('api.v1.auth.register'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('phone');

    expect(User::query()->where('phone', '+255700000001')->count())->toBe(1);
});

test('a donor can login using email or phone and the same device token is replaced', function () {
    $donor = User::factory()->donor()->create([
        'email' => 'donor@example.test',
        'phone' => '+255700000002',
    ]);

    foreach (['DONOR@EXAMPLE.TEST', '+255700000002'] as $identifier) {
        $this->postJson(route('api.v1.auth.login'), [
            'identifier' => $identifier,
            'password' => 'password',
        ])->assertOk()->assertJsonPath('user.id', $donor->id);
    }

    expect(PersonalAccessToken::query()->where('tokenable_id', $donor->id)->count())->toBe(1)
        ->and($donor->refresh()->donorProfile)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'mobile.password_authenticated')->count())->toBe(2);
});

test('canonical roles rather than the legacy role column control mobile access', function () {
    $donor = User::factory()->donor()->create([
        'email' => 'canonical-donor@example.test',
    ]);
    $donor->forceFill(['role' => 'admin'])->save();

    $staff = User::factory()->staff()->create([
        'email' => 'legacy-donor@example.test',
    ]);
    $staff->forceFill(['role' => 'donor'])->save();

    $this->postJson(route('api.v1.auth.login'), [
        'identifier' => $donor->email,
        'password' => 'password',
    ])->assertOk()->assertJsonPath('user.id', $donor->id);

    $this->postJson(route('api.v1.auth.login'), [
        'identifier' => $staff->email,
        'password' => 'password',
    ])->assertUnprocessable()->assertJsonValidationErrors('identifier');
});

test('invalid inactive and staff credentials all receive the same safe error', function () {
    $inactiveDonor = User::factory()->donor()->inactive()->create([
        'email' => 'inactive@example.test',
    ]);
    $staff = User::factory()->create(['email' => 'staff@example.test']);

    foreach ([
        ['missing@example.test', 'password'],
        [$inactiveDonor->email, 'password'],
        [$staff->email, 'password'],
        ['staff@example.test', 'wrong-password'],
    ] as [$identifier, $password]) {
        $this->postJson(route('api.v1.auth.login'), [
            'identifier' => $identifier,
            'password' => $password,
            'device_name' => 'Rejected Device',
        ], ['X-Locale' => 'sw'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('identifier')
            ->assertJsonPath('errors.identifier.0', 'Taarifa za kuingia kwenye programu si sahihi.');
    }

    expect(PersonalAccessToken::query()->count())->toBe(0);
});

test('registration validates donor fields and password confirmation', function () {
    $this->postJson(route('api.v1.auth.register'), [
        'name' => '',
        'phone' => '',
        'password' => 'short',
        'password_confirmation' => 'different',
        'blood_group' => 'X+',
        'gender' => 'unknown',
        'region' => '',
        'date_of_birth' => now()->addDay()->toDateString(),
    ])->assertUnprocessable()->assertJsonValidationErrors([
        'name',
        'phone',
        'password',
        'blood_group',
        'gender',
        'region',
        'date_of_birth',
    ]);

    expect(User::query()->count())->toBe(0);
});
