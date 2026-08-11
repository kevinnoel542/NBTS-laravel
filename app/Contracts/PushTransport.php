<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\UserNotification;

interface PushTransport
{
    /** @return array{provider: string, provider_message_id: string|null} */
    public function send(User $recipient, UserNotification $notification): array;
}
