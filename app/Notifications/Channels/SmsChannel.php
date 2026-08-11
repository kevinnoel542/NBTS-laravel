<?php

namespace App\Notifications\Channels;

use App\Contracts\SmsTransport;
use App\Models\SmsReminderLog;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\DonorCommunicationNotification;
use App\Services\Notifications\NotificationDeliveryTracker;
use Illuminate\Notifications\Notification;
use LogicException;
use Throwable;

final readonly class SmsChannel
{
    public function __construct(
        private NotificationDeliveryTracker $deliveryTracker,
        private SmsTransport $smsTransport,
    ) {}

    /** @return array{provider: string, provider_message_id: string|null} */
    public function send(object $notifiable, Notification $notification): array
    {
        if (! $notifiable instanceof User || ! $notification instanceof DonorCommunicationNotification) {
            throw new LogicException('The SMS channel requires an NBTS donor communication notification.');
        }

        $record = $notification->record();
        $delivery = $this->deliveryTracker->start($record, $notifiable, 'sms');

        try {
            $result = $this->smsTransport->send($notifiable, $record);
            $this->deliveryTracker->delivered(
                $delivery,
                $result['provider'],
                $result['provider_message_id'],
            );
            $this->updateReminderLog($record, 'sent', $result['provider'], $result['provider_message_id']);

            return $result;
        } catch (Throwable $exception) {
            $this->deliveryTracker->failed($delivery, $exception);
            $this->updateReminderLog($record, 'failed', null, null, $exception->getMessage());

            throw $exception;
        }
    }

    private function updateReminderLog(
        UserNotification $notification,
        string $status,
        ?string $provider,
        ?string $providerMessageId,
        ?string $error = null,
    ): void {
        $appointmentId = $notification->data['appointment_id'] ?? null;
        $reminderKey = $notification->data['reminder_key'] ?? null;

        if (! is_int($appointmentId) || ! is_string($reminderKey)) {
            return;
        }

        SmsReminderLog::query()
            ->where('appointment_id', $appointmentId)
            ->where('reminder_key', $reminderKey)
            ->update([
                'error' => $error === null ? null : mb_substr($error, 0, 2000),
                'provider' => $provider,
                'provider_message_id' => $providerMessageId,
                'sent_at' => $status === 'sent' ? now() : null,
                'status' => $status,
                'updated_at' => now(),
            ]);
    }
}
