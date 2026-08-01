<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JsonException;

class AuditLogger
{
    /**
     * Append a tamper-evident audit record.
     *
     * @param  array<string, mixed>  $metadata
     *
     * @throws JsonException
     */
    public function record(
        User $actor,
        string $action,
        ?Model $subject = null,
        ?BloodCenter $bloodCenter = null,
        array $metadata = [],
    ): AuditLog {
        return DB::transaction(function () use ($actor, $action, $subject, $bloodCenter, $metadata): AuditLog {
            $occurredAt = now()->toImmutable();
            $normalizedMetadata = $this->normalize($metadata);
            $previousHash = AuditLog::query()
                ->lockForUpdate()
                ->latest('id')
                ->value('record_hash');
            $payload = json_encode([
                'action' => $action,
                'actor_id' => $actor->id,
                'blood_center_id' => $bloodCenter?->id,
                'metadata' => $normalizedMetadata,
                'occurred_at' => $occurredAt->toIso8601String(),
                'previous_hash' => $previousHash,
                'subject_id' => $subject?->getKey(),
                'subject_type' => $subject?->getMorphClass(),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $request = app()->bound('request') ? request() : null;

            return AuditLog::unguarded(fn (): AuditLog => AuditLog::query()->create([
                'action' => $action,
                'actor_id' => $actor->id,
                'blood_center_id' => $bloodCenter?->id,
                'ip_address' => $request?->ip(),
                'metadata' => $normalizedMetadata,
                'occurred_at' => $occurredAt,
                'previous_hash' => $previousHash,
                'record_hash' => hash_hmac('sha256', $payload, (string) config('app.key')),
                'subject_id' => $subject?->getKey(),
                'subject_type' => $subject?->getMorphClass(),
                'user_agent' => $request?->userAgent(),
            ]));
        }, attempts: 3);
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->normalize($item), $value);
        }

        ksort($value);

        return array_map(fn (mixed $item): mixed => $this->normalize($item), $value);
    }
}
