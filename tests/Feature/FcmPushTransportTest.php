<?php

use App\Models\AuditLog;
use App\Models\FcmToken;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\FcmPushTransport;
use App\Support\AuditLogger;
use Database\Seeders\RolePermissionSeeder;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use Mockery\MockInterface;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('FCM HTTP v1 sends to active devices and retires provider-invalid tokens safely', function () {
    $recipient = User::factory()->donor()->create();
    $notification = UserNotification::factory()->create([
        'action_url' => '/appointments',
        'body' => 'Your donation appointment is tomorrow.',
        'title' => 'Appointment reminder',
        'type' => 'appointment_reminder',
        'user_id' => $recipient,
    ]);
    $validToken = 'valid-token-'.str_repeat('a', 80);
    $unknownToken = 'unknown-token-'.str_repeat('b', 80);
    FcmToken::factory()->create(['token' => $validToken, 'user_id' => $recipient]);
    FcmToken::factory()->create(['token' => $unknownToken, 'user_id' => $recipient]);
    $report = MulticastSendReport::withItems([
        SendReport::success(
            MessageTarget::with(MessageTarget::TOKEN, $validToken),
            ['name' => 'projects/nbts-d567e/messages/message-1'],
        ),
        SendReport::failure(
            MessageTarget::with(MessageTarget::TOKEN, $unknownToken),
            NotFound::becauseTokenNotFound($unknownToken),
        ),
    ]);
    $messaging = $this->mock(Messaging::class, function (MockInterface $mock) use ($report): void {
        $mock->shouldReceive('sendMulticast')->once()->andReturn($report);
    });

    $result = (new FcmPushTransport($messaging, app(AuditLogger::class)))
        ->send($recipient, $notification);

    expect($result)->toBe([
        'provider' => 'fcm_http_v1',
        'provider_message_id' => 'projects/nbts-d567e/messages/message-1',
    ])->and(FcmToken::query()->where('token', $validToken)->exists())->toBeTrue()
        ->and(FcmToken::query()->where('token', $unknownToken)->exists())->toBeFalse();

    $audit = AuditLog::query()->where('action', 'mobile.fcm_tokens_invalidated')->sole();

    expect($audit->metadata['count'])->toBe(1)
        ->and(json_encode($audit->metadata))->not->toContain($unknownToken)
        ->and($audit->metadata['token_fingerprints'])->toContain(hash('sha256', $unknownToken));
});

test('FCM delivery fails observably when every device token is invalid', function () {
    $recipient = User::factory()->donor()->create();
    $notification = UserNotification::factory()->create(['user_id' => $recipient]);
    $unknownToken = 'unknown-token-'.str_repeat('c', 80);
    FcmToken::factory()->create(['token' => $unknownToken, 'user_id' => $recipient]);
    $report = MulticastSendReport::withItems([
        SendReport::failure(
            MessageTarget::with(MessageTarget::TOKEN, $unknownToken),
            NotFound::becauseTokenNotFound($unknownToken),
        ),
    ]);
    $messaging = $this->mock(Messaging::class, function (MockInterface $mock) use ($report): void {
        $mock->shouldReceive('sendMulticast')->once()->andReturn($report);
    });
    $transport = new FcmPushTransport($messaging, app(AuditLogger::class));

    expect(fn () => $transport->send($recipient, $notification))
        ->toThrow(RuntimeException::class, 'did not complete delivery');

    expect(FcmToken::query()->where('token', $unknownToken)->exists())->toBeFalse();
});
