<?php

namespace App\Models;

use App\DonorReactionSeverity;
use Carbon\CarbonImmutable;
use Database\Factories\DonorReactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DonorReactionSeverity $severity
 * @property list<string> $symptoms
 * @property bool $followup_required
 * @property CarbonImmutable|null $followup_due_at
 * @property CarbonImmutable $occurred_at
 */
#[Fillable([
    'collection_episode_id',
    'donor_id',
    'blood_center_id',
    'severity',
    'reaction_type',
    'symptoms',
    'occurred_at',
    'treatment',
    'referral',
    'outcome',
    'followup_required',
    'followup_due_at',
    'recorded_by',
])]
class DonorReaction extends Model
{
    /** @use HasFactory<DonorReactionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'followup_due_at' => 'immutable_datetime',
            'followup_required' => 'boolean',
            'occurred_at' => 'immutable_datetime',
            'severity' => DonorReactionSeverity::class,
            'symptoms' => 'array',
        ];
    }

    /** @return BelongsTo<CollectionEpisode, $this> */
    public function collectionEpisode(): BelongsTo
    {
        return $this->belongsTo(CollectionEpisode::class);
    }

    /** @return BelongsTo<User, $this> */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
