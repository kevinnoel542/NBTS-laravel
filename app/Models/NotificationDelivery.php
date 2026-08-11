<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_notification_id
 * @property int $user_id
 * @property string $channel
 * @property string $status
 * @property int $attempts
 * @property string|null $provider
 * @property string|null $provider_message_id
 * @property string|null $last_error
 * @property CarbonImmutable|null $attempted_at
 * @property CarbonImmutable|null $delivered_at
 * @property CarbonImmutable|null $failed_at
 */
#[Fillable([
    'user_notification_id',
    'user_id',
    'channel',
    'status',
    'attempts',
    'provider',
    'provider_message_id',
    'last_error',
    'attempted_at',
    'delivered_at',
    'failed_at',
])]
class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<UserNotification, $this> */
    public function userNotification(): BelongsTo
    {
        return $this->belongsTo(UserNotification::class);
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
