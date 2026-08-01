<?php

namespace App\Actions\Notifications;

use App\DevicePlatform;
use App\Models\FcmToken;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

final readonly class RegisterFcmToken
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $user, string $token, DevicePlatform $devicePlatform): FcmToken
    {
        return DB::transaction(function () use ($user, $token, $devicePlatform): FcmToken {
            $fcmToken = FcmToken::query()
                ->where('token', $token)
                ->lockForUpdate()
                ->first();
            $previousUserId = $fcmToken?->user_id;
            $previousPlatform = $fcmToken?->device_type;
            $fcmToken ??= new FcmToken;
            $changed = ! $fcmToken->exists
                || $previousUserId !== $user->id
                || $previousPlatform !== $devicePlatform;

            $fcmToken->fill([
                'user_id' => $user->id,
                'token' => $token,
                'device_type' => $devicePlatform,
            ])->save();

            if ($changed) {
                $this->auditLogger->record(
                    actor: $user,
                    action: 'mobile.fcm_token_registered',
                    subject: $fcmToken,
                    metadata: [
                        'device_type' => $devicePlatform->value,
                        'reassigned' => $previousUserId !== null && $previousUserId !== $user->id,
                        'token_fingerprint' => hash('sha256', $token),
                    ],
                );
            }

            return $fcmToken;
        }, attempts: 3);
    }
}
