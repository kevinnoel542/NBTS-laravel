<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SmsReminderLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $appointment_id
 * @property int $user_id
 * @property string $reminder_key
 * @property string $phone
 * @property string $message
 * @property string|null $provider
 * @property string $status
 * @property string|null $provider_message_id
 * @property string|null $error
 * @property CarbonImmutable|null $sent_at
 */
#[Fillable([
    'appointment_id',
    'user_id',
    'reminder_key',
    'phone',
    'message',
    'provider',
    'status',
    'provider_message_id',
    'error',
    'sent_at',
])]
class SmsReminderLog extends Model
{
    /** @use HasFactory<SmsReminderLogFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
