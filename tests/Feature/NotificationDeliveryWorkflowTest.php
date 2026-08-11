<?php

use App\Actions\Response\SendDonorCommunication;
use App\AppointmentStatus;
use App\Contracts\PushTransport;
use App\Data\SendDonorCommunicationData;
use App\EligibilityStatus;
use App\Livewire\Operations\Workspace;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\DonorProfile;
use App\Models\FcmToken;
use App\Models\NotificationDelivery;
use App\Models\SmsReminderLog;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\Channels\InAppChannel;
use App\Notifications\Channels\PushChannel;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\TrackedMailChannel;
use App\Notifications\DonorCommunicationNotification;
use App\Services\AppointmentReminderService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('donor communication plans all consented channels and respects every channel opt out', function () {
    Notification::fake();

    $center = BloodCenter::factory()->create();
    $admin = User::factory()->nbtsAdmin()->create();
    $consentedDonor = User::factory()->donor()->create([
        'email' => 'consented@example.test',
        'phone' => '+255700000101',
    ]);
    $optedOutDonor = User::factory()->donor()->create([
        'email' => 'opted-out@example.test',
        'phone' => '+255700000102',
    ]);
    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'email_notifications_enabled' => true,
        'preferred_center_id' => $center,
        'push_notifications_enabled' => true,
        'sms_reminders_enabled' => true,
        'user_id' => $consentedDonor,
    ]);
    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'email_notifications_enabled' => false,
        'preferred_center_id' => $center,
        'push_notifications_enabled' => false,
        'sms_reminders_enabled' => false,
        'user_id' => $optedOutDonor,
    ]);
    FcmToken::factory()->create(['user_id' => $consentedDonor]);
    FcmToken::factory()->create(['user_id' => $optedOutDonor]);

    $recipientCount = app(SendDonorCommunication::class)->execute(
        $admin,
        new SendDonorCommunicationData(
            title: 'Center donor update',
            body: 'A concise operational donor message.',
            type: 'general',
            actionUrl: '/donate',
            bloodCenterId: $center->id,
            bloodGroup: null,
        ),
    );

    expect($recipientCount)->toBe(2);

    Notification::assertSentTo(
        $consentedDonor,
        DonorCommunicationNotification::class,
        fn (DonorCommunicationNotification $notification, array $channels): bool => collect([
            InAppChannel::class,
            PushChannel::class,
            SmsChannel::class,
            TrackedMailChannel::class,
        ])->every(fn (string $channel): bool => in_array($channel, $channels, true)),
    );
    Notification::assertSentTo(
        $optedOutDonor,
        DonorCommunicationNotification::class,
        fn (DonorCommunicationNotification $notification, array $channels): bool => $channels === [InAppChannel::class],
    );

    expect(NotificationDelivery::query()->where('user_id', $consentedDonor->id)->count())->toBe(4)
        ->and(NotificationDelivery::query()->where('user_id', $optedOutDonor->id)->count())->toBe(1)
        ->and(NotificationDelivery::query()->where('user_id', $optedOutDonor->id)->sole()->channel)->toBe('in_app');
});

test('appointment reminders are idempotent and keep opted out channels out of the delivery plan', function () {
    Notification::fake();

    $center = BloodCenter::factory()->create();
    $consentedDonor = User::factory()->donor()->create([
        'email' => 'reminder@example.test',
        'phone' => '+255700000201',
    ]);
    $optedOutDonor = User::factory()->donor()->create([
        'email' => 'quiet@example.test',
        'phone' => '+255700000202',
    ]);
    DonorProfile::factory()->create([
        'email_notifications_enabled' => true,
        'push_notifications_enabled' => true,
        'sms_reminders_enabled' => true,
        'user_id' => $consentedDonor,
    ]);
    DonorProfile::factory()->create([
        'email_notifications_enabled' => false,
        'push_notifications_enabled' => false,
        'sms_reminders_enabled' => false,
        'user_id' => $optedOutDonor,
    ]);
    FcmToken::factory()->create(['user_id' => $consentedDonor]);
    Appointment::factory()->count(2)->sequence(
        ['user_id' => $consentedDonor],
        ['user_id' => $optedOutDonor],
    )->create([
        'blood_center_id' => $center,
        'scheduled_at' => today()->addDay()->setTime(9, 30),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $service = app(AppointmentReminderService::class);

    expect($service->sendDueReminders(today()->toImmutable()))->toBe(2)
        ->and($service->sendDueReminders(today()->toImmutable()))->toBe(0)
        ->and(UserNotification::query()->where('type', 'appointment_reminder')->count())->toBe(2)
        ->and(SmsReminderLog::query()->count())->toBe(1);

    Notification::assertSentTo(
        $consentedDonor,
        DonorCommunicationNotification::class,
        fn (DonorCommunicationNotification $notification, array $channels): bool => in_array(SmsChannel::class, $channels, true)
            && in_array(PushChannel::class, $channels, true)
            && in_array(TrackedMailChannel::class, $channels, true),
    );
    Notification::assertSentTo(
        $optedOutDonor,
        DonorCommunicationNotification::class,
        fn (DonorCommunicationNotification $notification, array $channels): bool => $channels === [InAppChannel::class],
    );
});

test('a failed push attempt is observable and a later retry records delivery', function () {
    $recipient = User::factory()->donor()->create();
    $record = UserNotification::factory()->create(['user_id' => $recipient]);
    $transport = new class implements PushTransport
    {
        public int $attempts = 0;

        public function send(User $recipient, UserNotification $notification): array
        {
            $this->attempts++;

            if ($this->attempts === 1) {
                throw new RuntimeException('Temporary provider outage.');
            }

            return [
                'provider' => 'test-push',
                'provider_message_id' => 'provider-message-1',
            ];
        }
    };
    $this->app->instance(PushTransport::class, $transport);
    $channel = app(PushChannel::class);
    $notification = new DonorCommunicationNotification($record->id);

    expect(fn () => $channel->send($recipient, $notification))
        ->toThrow(RuntimeException::class);

    $failedDelivery = NotificationDelivery::query()->sole();

    expect($failedDelivery->status)->toBe('failed')
        ->and($failedDelivery->attempts)->toBe(1)
        ->and($failedDelivery->last_error)->toContain('Temporary provider outage');

    $channel->send($recipient, $notification);

    expect($failedDelivery->refresh()->status)->toBe('delivered')
        ->and($failedDelivery->attempts)->toBe(2)
        ->and($failedDelivery->provider)->toBe('test-push')
        ->and($failedDelivery->provider_message_id)->toBe('provider-message-1')
        ->and($failedDelivery->last_error)->toBeNull();
});

test('an administrator can filter delivery failures in the engagement workspace', function () {
    $center = BloodCenter::factory()->create();
    $admin = User::factory()->nbtsAdmin()->create();
    $recipient = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'preferred_center_id' => $center,
        'user_id' => $recipient,
    ]);
    $notification = UserNotification::factory()->create([
        'title' => 'Urgent O negative request',
        'user_id' => $recipient,
    ]);
    NotificationDelivery::factory()->create([
        'channel' => 'push',
        'last_error' => 'Provider is temporarily unavailable.',
        'status' => 'failed',
        'user_id' => $recipient,
        'user_notification_id' => $notification,
    ]);

    Livewire::actingAs($admin)
        ->test(Workspace::class, ['workspace' => 'engagement'])
        ->set('tab', 'deliveries')
        ->set('statusFilter', 'failed')
        ->assertSee('Urgent O negative request')
        ->assertSee('Provider is temporarily unavailable.')
        ->assertSee('Failed')
        ->assertSee('Delivery status')
        ->assertHasNoErrors();
});
