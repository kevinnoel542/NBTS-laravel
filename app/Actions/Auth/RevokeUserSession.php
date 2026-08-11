<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use LogicException;

final readonly class RevokeUserSession
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $user, string $sessionId, string $currentSessionId): bool
    {
        if (hash_equals($currentSessionId, $sessionId)) {
            throw new LogicException('The current session cannot be revoked from the active session list.');
        }

        return DB::transaction(function () use ($user, $sessionId): bool {
            $session = DB::table($this->sessionTable())
                ->where('id', $sessionId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first(['id', 'ip_address', 'user_agent']);

            if ($session === null) {
                return false;
            }

            DB::table($this->sessionTable())
                ->where('id', $sessionId)
                ->where('user_id', $user->id)
                ->delete();

            $this->auditLogger->record(
                actor: $user,
                action: 'account.session_revoked',
                subject: $user,
                metadata: [
                    'session_fingerprint' => hash('sha256', $sessionId),
                    'target_ip_address' => $session->ip_address,
                    'target_user_agent' => $session->user_agent,
                ],
            );

            return true;
        }, attempts: 3);
    }

    public function revokeOthers(User $user, string $currentSessionId): int
    {
        return DB::transaction(function () use ($user, $currentSessionId): int {
            $sessions = DB::table($this->sessionTable())
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->lockForUpdate()
                ->get(['id']);

            if ($sessions->isEmpty()) {
                return 0;
            }

            $deleted = DB::table($this->sessionTable())
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();

            $this->auditLogger->record(
                actor: $user,
                action: 'account.other_sessions_revoked',
                subject: $user,
                metadata: [
                    'revoked_count' => $deleted,
                    'session_fingerprints' => $sessions
                        ->map(fn (object $session): string => hash('sha256', (string) $session->id))
                        ->all(),
                ],
            );

            return $deleted;
        }, attempts: 3);
    }

    private function sessionTable(): string
    {
        return (string) config('session.table', 'sessions');
    }
}
