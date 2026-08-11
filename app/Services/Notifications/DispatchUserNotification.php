<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\DonorCommunicationNotification;

final readonly class DispatchUserNotification
{
    public function __construct(private NotificationDeliveryTracker $deliveryTracker) {}

    public function execute(UserNotification $record, User $recipient): void
    {
        $notification = new DonorCommunicationNotification($record->id);

        foreach ($notification->plannedChannels($recipient) as $channel) {
            if ($channel === 'in_app') {
                $this->deliveryTracker->recordInApp($record, $recipient);

                continue;
            }

            $this->deliveryTracker->ensure($record, $recipient, $channel);
        }

        $recipient->notify($notification->afterCommit());
    }
}
