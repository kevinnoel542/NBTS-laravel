<?php

namespace App\Models;

use App\DonorRewardStatus;
use Carbon\CarbonImmutable;
use Database\Factories\DonorRewardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $reward_id
 * @property DonorRewardStatus $status
 * @property CarbonImmutable $awarded_at
 * @property CarbonImmutable|null $redeemed_at
 */
#[Fillable([
    'user_id',
    'reward_id',
    'status',
    'awarded_at',
    'redeemed_at',
])]
class DonorReward extends Model
{
    /** @use HasFactory<DonorRewardFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
            'redeemed_at' => 'datetime',
            'status' => DonorRewardStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Reward, $this> */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}
