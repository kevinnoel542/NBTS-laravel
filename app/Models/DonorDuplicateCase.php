<?php

namespace App\Models;

use App\DonorDuplicateCaseStatus;
use Carbon\CarbonImmutable;
use Database\Factories\DonorDuplicateCaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property DonorDuplicateCaseStatus $status
 * @property array<string, bool> $match_signals
 * @property string $match_score
 * @property CarbonImmutable|null $reviewed_at
 */
#[Fillable([
    'primary_donor_id',
    'candidate_donor_id',
    'blood_center_id',
    'status',
    'match_signals',
    'match_score',
    'detected_by',
    'reviewed_by',
    'reviewed_at',
    'review_reason',
])]
class DonorDuplicateCase extends Model
{
    /** @use HasFactory<DonorDuplicateCaseFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'match_score' => 'decimal:2',
            'match_signals' => 'array',
            'reviewed_at' => 'immutable_datetime',
            'status' => DonorDuplicateCaseStatus::class,
        ];
    }

    /**
     * @param  Builder<DonorDuplicateCase>  $query
     * @return Builder<DonorDuplicateCase>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', DonorDuplicateCaseStatus::Pending);
    }

    /** @return BelongsTo<User, $this> */
    public function primaryDonor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_donor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function candidateDonor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_donor_id');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function detector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'detected_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return HasOne<DonorIdentityAlias, $this> */
    public function identityAlias(): HasOne
    {
        return $this->hasOne(DonorIdentityAlias::class, 'duplicate_case_id');
    }
}
