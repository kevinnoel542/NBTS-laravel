<?php

namespace App\Notifications\Channels;

use App\Contracts\PushTransport;
use App\Models\User;
use App\Notifications\DonorCommunicationNotification;
use App\Services\Notifications\NotificationDeliveryTracker;
use Illuminate\Notifications\Notification;
use LogicException;
use Throwable;

final readonly class PushChannel
{
    public function __construct(
        private NotificationDeliveryTracker $deliveryTracker,
        private PushTransport $pushTransport,
    ) {}

    /** @return array{provider: string, provider_message_id: string|null} */
    public function send(object $notifiable, Notification $notification): array
    {
        if (! $notifiable instanceof User || ! $notification instanceof DonorCommunicationNotification) {
            throw new LogicException('The push channel requires an NBTS donor communication notification.');
        }

        $record = $notification->record();
        $delivery = $this->deliveryTracker->start($record, $notifiable, 'push');

        try {
            $result = $this->pushTransport->send($notifiable, $record);
            $this->deliveryTracker->delivered(
                $delivery,
                $result['provider'],
                $result['provider_message_id'],
            );

            return $result;
        } catch (Throwable $exception) {
            $this->deliveryTracker->failed($delivery, $exception);

            throw $exception;
        }
    }
}
