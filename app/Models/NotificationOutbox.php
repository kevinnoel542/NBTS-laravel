<?php

namespace App\Models;

use Database\Factories\NotificationOutboxFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'created_by',
    'recipient_id',
    'user_notification_id',
    'outbox_reference',
    'idempotency_key',
    'template_code',
    'alert_type',
    'channel',
    'locale',
    'recipient_hash',
    'segment_criteria',
    'payload_summary',
    'preferences_snapshot',
    'consent_snapshot',
    'quiet_hours',
    'after_commit',
    'non_coercive',
    'provider',
    'provider_message_id',
    'status',
    'attempts',
    'max_attempts',
    'next_attempt_at',
    'sent_at',
    'failed_at',
    'expires_at',
    'last_error',
])]
class NotificationOutbox extends Model
{
    /** @use HasFactory<NotificationOutboxFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'after_commit' => 'boolean',
            'consent_snapshot' => 'array',
            'expires_at' => 'immutable_datetime',
            'failed_at' => 'immutable_datetime',
            'next_attempt_at' => 'immutable_datetime',
            'non_coercive' => 'boolean',
            'payload_summary' => 'array',
            'preferences_snapshot' => 'array',
            'quiet_hours' => 'array',
            'segment_criteria' => 'array',
            'sent_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /** @return BelongsTo<UserNotification, $this> */
    public function userNotification(): BelongsTo
    {
        return $this->belongsTo(UserNotification::class);
    }
}
