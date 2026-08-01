<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\DonorBadgeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $badge_id
 * @property CarbonImmutable $awarded_at
 */
#[Fillable([
    'user_id',
    'badge_id',
    'awarded_at',
])]
class DonorBadge extends Model
{
    /** @use HasFactory<DonorBadgeFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Badge, $this> */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
