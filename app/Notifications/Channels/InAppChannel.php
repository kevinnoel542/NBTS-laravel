<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Notifications\DonorCommunicationNotification;
use App\Services\Notifications\NotificationDeliveryTracker;
use Illuminate\Notifications\Notification;
use LogicException;

final readonly class InAppChannel
{
    public function __construct(private NotificationDeliveryTracker $deliveryTracker) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof User || ! $notification instanceof DonorCommunicationNotification) {
            throw new LogicException('The in-app channel requires an NBTS donor communication notification.');
        }

        $this->deliveryTracker->recordInApp($notification->record(), $notifiable);
    }
}
