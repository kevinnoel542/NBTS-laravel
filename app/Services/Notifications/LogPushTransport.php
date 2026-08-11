<?php

namespace App\Services\Notifications;

use App\Contracts\PushTransport;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class LogPushTransport implements PushTransport
{
    public function send(User $recipient, UserNotification $notification): array
    {
        $providerMessageId = 'log-push-'.Str::uuid();

        Log::info('Construction-mode push notification recorded.', [
            'notification_id' => $notification->id,
            'provider_message_id' => $providerMessageId,
            'recipient_id' => $recipient->id,
            'token_count' => $recipient->fcmTokens()->count(),
        ]);

        return [
            'provider' => 'log',
            'provider_message_id' => $providerMessageId,
        ];
    }
}
