<?php

use App\Models\AuditLog;
use App\Models\FcmToken;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function notificationApiToken(User $donor, array $abilities = ['donor:read', 'donor:write']): string
{
    return $donor->createToken('Notification Phone', $abilities)->plainTextToken;
}

test('a donor can list only their notifications with unread metadata and filters', function () {
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $latest = UserNotification::factory()->create([
        'user_id' => $donor,
        'title' => 'Appointment confirmed',
        'body' => 'Your appointment is confirmed.',
        'type' => 'appointment',
        'created_at' => now(),
    ]);
    UserNotification::factory()->create([
        'user_id' => $donor,
        'type' => 'campaign',
        'created_at' => now()->subMinute(),
    ]);
    UserNotification::factory()->read()->create([
        'user_id' => $donor,
        'type' => 'appointment',
    ]);
    UserNotification::factory()->create(['user_id' => $otherDonor]);

    $this->withToken(notificationApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.notifications.index', [
            'unread' => 1,
            'type' => 'appointment',
            'per_page' => 1,
        ]))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $latest->id)
        ->assertJsonPath('data.0.message', 'Your appointment is confirmed.')
        ->assertJsonPath('data.0.read', false)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.unread_count', 2);
});

test('a donor can retrieve the unread notification count', function () {
    $donor = User::factory()->donor()->create();
    UserNotification::factory()->count(2)->create(['user_id' => $donor]);
    UserNotification::factory()->read()->create(['user_id' => $donor]);

    $this->withToken(notificationApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('data.unread_count', 2);
});

test('a donor can mark one owned notification read but cannot address another donor record', function () {
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $notification = UserNotification::factory()->create(['user_id' => $donor]);
    $otherNotification = UserNotification::factory()->create(['user_id' => $otherDonor]);
    $token = notificationApiToken($donor);

    $this->withToken($token)
        ->postJson(route('api.v1.notifications.read', $notification))
        ->assertOk()
        ->assertJsonPath('data.read', true);

    expect($notification->refresh()->read_at)->not->toBeNull();

    $this->app['auth']->forgetGuards();
    $this->withToken($token)
        ->postJson(route('api.v1.notifications.read', $otherNotification))
        ->assertNotFound();
});

test('mark all read updates only the authenticated donor notifications', function () {
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    UserNotification::factory()->count(3)->create(['user_id' => $donor]);
    $otherNotification = UserNotification::factory()->create(['user_id' => $otherDonor]);

    $this->withToken(notificationApiToken($donor))
        ->postJson(route('api.v1.notifications.mark-all-read'))
        ->assertOk()
        ->assertJsonPath('data.unread_count', 0)
        ->assertJsonPath('data.updated_count', 3);

    expect($donor->userNotifications()->whereNull('read_at')->count())->toBe(0)
        ->and($otherNotification->refresh()->read_at)->toBeNull();
});

test('a donor can delete an owned notification without deleting another donor record', function () {
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $notification = UserNotification::factory()->create(['user_id' => $donor]);
    $otherNotification = UserNotification::factory()->create(['user_id' => $otherDonor]);
    $token = notificationApiToken($donor);

    $this->withToken($token)
        ->deleteJson(route('api.v1.notifications.destroy', $notification))
        ->assertOk()
        ->assertJsonPath('data.id', $notification->id)
        ->assertJsonPath('data.unread_count', 0);

    $this->assertDatabaseMissing('user_notifications', ['id' => $notification->id]);

    $this->app['auth']->forgetGuards();
    $this->withToken($token)
        ->deleteJson(route('api.v1.notifications.destroy', $otherNotification))
        ->assertNotFound();
    $this->assertDatabaseHas('user_notifications', ['id' => $otherNotification->id]);
});

test('fcm registration is deduplicated reassignable and audited without the raw token', function () {
    $donor = User::factory()->donor()->create();
    $previousOwner = User::factory()->donor()->create();
    $fcmTokenValue = str_repeat('aB9_', 40);
    $existingToken = FcmToken::factory()->create([
        'user_id' => $previousOwner,
        'token' => $fcmTokenValue,
    ]);
    $apiToken = notificationApiToken($donor);

    $this->withToken($apiToken)
        ->postJson(route('api.v1.notifications.register-token'), [
            'token' => $fcmTokenValue,
            'device_type' => 'ios',
        ])->assertOk()
        ->assertJsonPath('data.id', $existingToken->id)
        ->assertJsonPath('data.device_type', 'ios')
        ->assertJsonPath('data.registered', true);

    expect($existingToken->refresh()->user_id)->toBe($donor->id)
        ->and($existingToken->device_type->value)->toBe('ios')
        ->and(AuditLog::query()->where('action', 'mobile.fcm_token_registered')->count())->toBe(1)
        ->and(json_encode(AuditLog::query()->latest('id')->value('metadata')))->not->toContain($fcmTokenValue);

    $this->app['auth']->forgetGuards();
    $this->withToken($apiToken)
        ->postJson(route('api.v1.notifications.register-token'), [
            'token' => $fcmTokenValue,
            'device_type' => 'ios',
        ])->assertOk();

    expect(FcmToken::query()->where('token', $fcmTokenValue)->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'mobile.fcm_token_registered')->count())->toBe(1);
});

test('fcm unregistration is owner scoped idempotent and audited', function () {
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $ownedToken = FcmToken::factory()->create(['user_id' => $donor]);
    $otherToken = FcmToken::factory()->create(['user_id' => $otherDonor]);
    $apiToken = notificationApiToken($donor);

    $this->withToken($apiToken)
        ->deleteJson(route('api.v1.notifications.unregister-token'), ['token' => $ownedToken->token])
        ->assertNoContent();

    expect(FcmToken::query()->whereKey($ownedToken->id)->exists())->toBeFalse()
        ->and(AuditLog::query()->where('action', 'mobile.fcm_token_unregistered')->count())->toBe(1);

    $this->app['auth']->forgetGuards();
    $this->withToken($apiToken)
        ->deleteJson(route('api.v1.notifications.unregister-token'), ['token' => $otherToken->token])
        ->assertNoContent();

    expect(FcmToken::query()->whereKey($otherToken->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'mobile.fcm_token_unregistered')->count())->toBe(1);
});

test('notification mutations require donor write ability and valid device data', function () {
    $donor = User::factory()->donor()->create();
    $notification = UserNotification::factory()->create(['user_id' => $donor]);
    $readOnlyToken = notificationApiToken($donor, ['donor:read']);

    $this->getJson(route('api.v1.notifications.index'))->assertUnauthorized();

    $this->withToken($readOnlyToken)
        ->postJson(route('api.v1.notifications.read', $notification))
        ->assertForbidden();

    $this->app['auth']->forgetGuards();
    $this->withToken(notificationApiToken($donor))
        ->postJson(route('api.v1.notifications.register-token'), [
            'token' => 'too-short',
            'device_type' => 'desktop',
        ])->assertUnprocessable()
        ->assertJsonValidationErrors(['token', 'device_type']);
});
