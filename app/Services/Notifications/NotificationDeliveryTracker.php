<?php

namespace App\Services\Notifications;

use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Throwable;

final class NotificationDeliveryTracker
{
    public function ensure(UserNotification $notification, User $recipient, string $channel): NotificationDelivery
    {
        return NotificationDelivery::query()->firstOrCreate(
            [
                'channel' => $channel,
                'user_notification_id' => $notification->id,
            ],
            [
                'attempts' => 0,
                'status' => 'pending',
                'user_id' => $recipient->id,
            ],
        );
    }

    public function start(UserNotification $notification, User $recipient, string $channel): NotificationDelivery
    {
        $delivery = $this->ensure($notification, $recipient, $channel);

        return DB::transaction(function () use ($delivery): NotificationDelivery {
            $lockedDelivery = NotificationDelivery::query()->lockForUpdate()->findOrFail($delivery->id);
            $lockedDelivery->forceFill([
                'attempted_at' => now(),
                'attempts' => $lockedDelivery->attempts + 1,
                'failed_at' => null,
                'last_error' => null,
                'status' => 'processing',
            ])->save();

            return $lockedDelivery->refresh();
        }, attempts: 3);
    }

    public function delivered(
        NotificationDelivery $delivery,
        string $provider,
        ?string $providerMessageId = null,
    ): NotificationDelivery {
        $delivery->forceFill([
            'delivered_at' => now(),
            'failed_at' => null,
            'last_error' => null,
            'provider' => $provider,
            'provider_message_id' => $providerMessageId,
            'status' => 'delivered',
        ])->save();

        return $delivery->refresh();
    }

    public function failed(NotificationDelivery $delivery, Throwable $exception): NotificationDelivery
    {
        $delivery->forceFill([
            'failed_at' => now(),
            'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            'status' => 'failed',
        ])->save();

        return $delivery->refresh();
    }

    public function recordInApp(UserNotification $notification, User $recipient): NotificationDelivery
    {
        $delivery = $this->ensure($notification, $recipient, 'in_app');

        if ($delivery->status === 'delivered') {
            return $delivery;
        }

        $delivery->forceFill([
            'attempted_at' => now(),
            'attempts' => max(1, $delivery->attempts),
            'delivered_at' => now(),
            'provider' => 'database',
            'status' => 'delivered',
        ])->save();

        $notification->forceFill(['sent_at' => $notification->sent_at ?? now()])->save();

        return $delivery->refresh();
    }
}
