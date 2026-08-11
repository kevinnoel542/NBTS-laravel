<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\UserNotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property string $type
 * @property string|null $source_key
 * @property string|null $action_url
 * @property array<string, mixed>|null $data
 * @property CarbonImmutable|null $read_at
 * @property CarbonImmutable|null $sent_at
 * @property CarbonImmutable|null $created_at
 */
#[Fillable([
    'user_id',
    'title',
    'body',
    'type',
    'source_key',
    'action_url',
    'data',
    'read_at',
    'sent_at',
])]
class UserNotification extends Model
{
    /** @use HasFactory<UserNotificationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function markAsRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->update(['read_at' => now()]);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<NotificationDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
