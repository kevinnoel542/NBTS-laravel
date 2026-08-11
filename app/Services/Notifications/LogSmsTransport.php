<?php

namespace App\Services\Notifications;

use App\Contracts\SmsTransport;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class LogSmsTransport implements SmsTransport
{
    public function send(User $recipient, UserNotification $notification): array
    {
        $providerMessageId = 'log-sms-'.Str::uuid();

        Log::info('Construction-mode SMS notification recorded.', [
            'notification_id' => $notification->id,
            'provider_message_id' => $providerMessageId,
            'recipient_id' => $recipient->id,
        ]);

        return [
            'provider' => 'log',
            'provider_message_id' => $providerMessageId,
        ];
    }
}
