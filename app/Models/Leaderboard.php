<?php

namespace App\Models;

use Database\Factories\LeaderboardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $period
 * @property int $donation_count
 * @property int|null $rank
 */
#[Fillable([
    'user_id',
    'period',
    'donation_count',
    'rank',
])]
class Leaderboard extends Model
{
    /** @use HasFactory<LeaderboardFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
