<?php

namespace App\Actions\Notifications;

use App\Models\FcmToken;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

final readonly class UnregisterFcmToken
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $user, string $token): bool
    {
        return DB::transaction(function () use ($user, $token): bool {
            $fcmToken = FcmToken::query()
                ->where('user_id', $user->id)
                ->where('token', $token)
                ->lockForUpdate()
                ->first();

            if ($fcmToken === null) {
                return false;
            }

            $this->auditLogger->record(
                actor: $user,
                action: 'mobile.fcm_token_unregistered',
                subject: $fcmToken,
                metadata: [
                    'device_type' => $fcmToken->device_type->value,
                    'token_fingerprint' => hash('sha256', $token),
                ],
            );
            $fcmToken->delete();

            return true;
        }, attempts: 3);
    }
}
