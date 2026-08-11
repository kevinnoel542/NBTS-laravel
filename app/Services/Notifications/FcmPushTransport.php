<?php

namespace App\Services\Notifications;

use App\Contracts\PushTransport;
use App\Models\FcmToken;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\RegistrationTokens;
use RuntimeException;

final readonly class FcmPushTransport implements PushTransport
{
    public function __construct(
        private Messaging $messaging,
        private AuditLogger $auditLogger,
    ) {}

    /** @return array{provider: string, provider_message_id: string|null} */
    public function send(User $recipient, UserNotification $notification): array
    {
        $tokens = $recipient->fcmTokens()
            ->get(['token'])
            ->map(static fn (FcmToken $fcmToken): string => $fcmToken->token)
            ->values()
            ->all();

        if ($tokens === []) {
            throw new RuntimeException('The donor has no active Firebase registration token.');
        }

        $registrationTokens = RegistrationTokens::fromValue($tokens);

        $message = CloudMessage::new()
            ->withNotification([
                'title' => $notification->title,
                'body' => $notification->body,
            ])
            ->withData([
                'action_url' => (string) ($notification->action_url ?? ''),
                'notification_id' => (string) $notification->id,
                'type' => $notification->type,
            ]);
        $report = $this->messaging->sendMulticast($message, $registrationTokens);
        $invalidTokens = array_values(array_unique([
            ...$report->invalidTokens(),
            ...$report->unknownTokens(),
        ]));

        if ($invalidTokens !== []) {
            $this->retireInvalidTokens($recipient, $invalidTokens);
        }

        $successes = $report->successes();
        $unhandledFailureCount = $report->failures()->count() - count($invalidTokens);

        if ($unhandledFailureCount > 0 || $successes->count() === 0) {
            throw new RuntimeException('Firebase Cloud Messaging did not complete delivery to every valid device.');
        }

        $providerMessageId = null;

        foreach ($successes->getItems() as $sendReport) {
            $messageId = $sendReport->result()['name'] ?? null;

            if (is_string($messageId)) {
                $providerMessageId = $messageId;

                break;
            }
        }

        return [
            'provider' => 'fcm_http_v1',
            'provider_message_id' => $providerMessageId,
        ];
    }

    /** @param list<string> $invalidTokens */
    private function retireInvalidTokens(User $recipient, array $invalidTokens): void
    {
        DB::transaction(function () use ($recipient, $invalidTokens): void {
            $deletedCount = FcmToken::query()
                ->where('user_id', $recipient->id)
                ->whereIn('token', $invalidTokens)
                ->delete();

            if ($deletedCount === 0) {
                return;
            }

            $this->auditLogger->record(
                actor: $recipient,
                action: 'mobile.fcm_tokens_invalidated',
                subject: $recipient,
                metadata: [
                    'count' => $deletedCount,
                    'token_fingerprints' => collect($invalidTokens)
                        ->map(static fn (string $token): string => hash('sha256', $token))
                        ->all(),
                ],
            );
        }, attempts: 3);
    }
}
