<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Notifications\DonorCommunicationNotification;
use App\Services\Notifications\NotificationDeliveryTracker;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use LogicException;
use Throwable;

final readonly class TrackedMailChannel
{
    public function __construct(
        private NotificationDeliveryTracker $deliveryTracker,
        private MailChannel $mailChannel,
    ) {}

    public function send(object $notifiable, Notification $notification): mixed
    {
        if (! $notifiable instanceof User || ! $notification instanceof DonorCommunicationNotification) {
            throw new LogicException('The tracked mail channel requires an NBTS donor communication notification.');
        }

        $record = $notification->record();
        $delivery = $this->deliveryTracker->start($record, $notifiable, 'email');

        try {
            $response = $this->mailChannel->send($notifiable, $notification);
            $this->deliveryTracker->delivered($delivery, (string) config('mail.default', 'mail'));

            return $response;
        } catch (Throwable $exception) {
            $this->deliveryTracker->failed($delivery, $exception);

            throw $exception;
        }
    }
}
