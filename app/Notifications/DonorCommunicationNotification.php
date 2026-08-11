<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\Channels\InAppChannel;
use App\Notifications\Channels\PushChannel;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\TrackedMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DonorCommunicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $userNotificationId) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof User
            ? array_keys($this->plannedChannels($notifiable))
            : [];
    }

    /** @return array<class-string, string> */
    public function plannedChannels(User $recipient): array
    {
        if (! $recipient->is_active) {
            return [];
        }

        $recipient->loadMissing(['donorProfile', 'fcmTokens']);
        $profile = $recipient->donorProfile;
        $channels = [InAppChannel::class => 'in_app'];

        if ($profile?->push_notifications_enabled && $recipient->fcmTokens->isNotEmpty()) {
            $channels[PushChannel::class] = 'push';
        }

        if ($profile?->sms_reminders_enabled && filled($recipient->phone)) {
            $channels[SmsChannel::class] = 'sms';
        }

        if ($profile?->email_notifications_enabled && filled($recipient->email)) {
            $channels[TrackedMailChannel::class] = 'email';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $record = $this->record();
        $message = (new MailMessage)
            ->subject($record->title)
            ->greeting(__('console.notifications.greeting', [
                'name' => $notifiable instanceof User ? $notifiable->name : '',
            ]))
            ->line($record->body);

        if (filled($record->action_url)) {
            $actionUrl = Str::startsWith((string) $record->action_url, ['http://', 'https://'])
                ? (string) $record->action_url
                : url((string) $record->action_url);
            $message->action(__('console.notifications.open_action'), $actionUrl);
        }

        return $message->line(__('console.notifications.footer'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_notification_id' => $this->userNotificationId,
        ];
    }

    /** @return array<class-string|string, string> */
    public function viaQueues(): array
    {
        return [
            InAppChannel::class => 'notifications-in-app',
            PushChannel::class => 'notifications-push',
            SmsChannel::class => 'notifications-sms',
            TrackedMailChannel::class => 'notifications-mail',
        ];
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Queued donor communication exhausted its retries.', [
            'error' => $exception?->getMessage(),
            'user_notification_id' => $this->userNotificationId,
        ]);
    }

    public function record(): UserNotification
    {
        return UserNotification::query()->findOrFail($this->userNotificationId);
    }
}
