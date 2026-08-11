<?php

use App\Livewire\Settings\Security;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
    Features::passkeys([
        'confirmPassword' => true,
    ]);
});

test('security settings page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'));

    $response->assertOk();

    $response->assertSee('Passkeys');
    $response->assertSee('No passkeys yet');
    $response->assertSee('Two-factor authentication');
    $response->assertSee('Enable 2FA');
});

test('security settings page requires password confirmation when enabled', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('security.edit'));

    $response->assertRedirect(route('password.confirm'));
});

test('security settings page renders without two factor when feature is disabled', function () {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Update password')
        ->assertDontSee('Manage your passkeys for passwordless sign-in')
        ->assertDontSee('Add a passkey to sign in without a password')
        ->assertDontSee('Two-factor authentication');
});

test('two factor authentication disabled when confirmation abandoned between requests', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test(Security::class);

    $component->assertSet('twoFactorEnabled', false);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Security::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Security::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasErrors(['current_password']);
});

test('active database sessions are listed with device context', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $this->actingAs($user);

    DB::table('sessions')->insert([
        'id' => 'firefox-mobile-session',
        'user_id' => $user->id,
        'ip_address' => '192.0.2.24',
        'user_agent' => 'Mozilla/5.0 (Android 14; Mobile; rv:126.0) Gecko/126.0 Firefox/126.0',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->getTimestamp(),
    ]);

    Livewire::test(Security::class)
        ->assertSee('Active sessions')
        ->assertSee('Firefox')
        ->assertSee('Android')
        ->assertSee('192.0.2.24')
        ->assertSee('This device');
});

test('a user can revoke another session after confirming their password', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);
    $this->actingAs($user);

    DB::table('sessions')->insert([
        'id' => 'unrecognized-session',
        'user_id' => $user->id,
        'ip_address' => '198.51.100.18',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/126.0',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->getTimestamp(),
    ]);

    Livewire::test(Security::class)
        ->call('confirmSessionRevocation', 'unrecognized-session')
        ->assertSet('showSessionModal', true)
        ->set('session_password', 'correct-password')
        ->call('revokeSessions')
        ->assertHasNoErrors()
        ->assertSet('showSessionModal', false);

    $this->assertDatabaseMissing('sessions', ['id' => 'unrecognized-session']);
    $this->assertDatabaseHas('audit_logs', [
        'actor_id' => $user->id,
        'action' => 'account.session_revoked',
        'subject_id' => $user->id,
        'subject_type' => $user->getMorphClass(),
    ]);
});

test('another users session cannot be selected or revoked', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user);

    DB::table('sessions')->insert([
        'id' => 'other-users-session',
        'user_id' => $otherUser->id,
        'ip_address' => '203.0.113.45',
        'user_agent' => 'Mozilla/5.0 (Macintosh) AppleWebKit/605.1.15 Safari/605.1.15',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->getTimestamp(),
    ]);

    Livewire::test(Security::class)
        ->call('confirmSessionRevocation', 'other-users-session')
        ->assertSet('showSessionModal', false);

    $this->assertDatabaseHas('sessions', [
        'id' => 'other-users-session',
        'user_id' => $otherUser->id,
    ]);
    expect(AuditLog::query()->where('action', 'account.session_revoked')->exists())->toBeFalse();
});

test('all other sessions require a valid current password and preserve this device', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);
    $this->actingAs($user);

    foreach (['other-session-one', 'other-session-two'] as $sessionId) {
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => '198.51.100.8',
            'user_agent' => 'Mozilla/5.0 (Linux) AppleWebKit/537.36 Chrome/126.0',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->getTimestamp(),
        ]);
    }

    $component = Livewire::test(Security::class)
        ->call('confirmOtherSessionRevocation')
        ->assertSet('revokeAllOtherSessions', true)
        ->set('session_password', 'incorrect-password')
        ->call('revokeSessions')
        ->assertHasErrors(['session_password']);

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(2);

    $component
        ->set('session_password', 'correct-password')
        ->call('revokeSessions')
        ->assertHasNoErrors()
        ->assertSee('This device');

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'account.other_sessions_revoked')->count())->toBe(1);
});
