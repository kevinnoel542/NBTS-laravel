<?php

namespace App\Models;

use App\CollectionEpisodeStatus;
use App\CollectionOutcome;
use App\Services\ActiveAssignmentContext;
use Carbon\CarbonImmutable;
use Database\Factories\CollectionEpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CollectionEpisodeStatus $status
 * @property CollectionOutcome|null $outcome
 * @property int $donor_id
 * @property int $blood_center_id
 * @property int $planned_volume_ml
 * @property int|null $actual_volume_ml
 * @property CarbonImmutable|null $started_at
 * @property CarbonImmutable|null $ended_at
 */
#[Fillable([
    'donation_identifier',
    'donor_id',
    'blood_center_id',
    'appointment_id',
    'identity_check_id',
    'eligibility_record_id',
    'donation_id',
    'status',
    'outcome',
    'donation_method',
    'bag_type',
    'bag_lot',
    'device_identifier',
    'planned_volume_ml',
    'actual_volume_ml',
    'started_at',
    'ended_at',
    'prepared_by',
    'collected_by',
    'source_mode',
    'aftercare_confirmed_at',
    'donor_acknowledged_at',
    'notes',
])]
class CollectionEpisode extends Model
{
    /** @use HasFactory<CollectionEpisodeFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'actual_volume_ml' => 'integer',
            'aftercare_confirmed_at' => 'immutable_datetime',
            'donor_acknowledged_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'outcome' => CollectionOutcome::class,
            'planned_volume_ml' => 'integer',
            'started_at' => 'immutable_datetime',
            'status' => CollectionEpisodeStatus::class,
        ];
    }

    /**
     * @param  Builder<CollectionEpisode>  $query
     * @return Builder<CollectionEpisode>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasNationalScope()) {
            return $query;
        }

        $assignment = app(ActiveAssignmentContext::class)->selectedAssignment($user);
        $selectedCenterId = $assignment?->organizationUnit->bloodCenter?->id;

        return $selectedCenterId === null
            ? $query->whereRaw('1 = 0')
            : $query->where('blood_center_id', $selectedCenterId);
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

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<DonorIdentityCheck, $this> */
    public function identityCheck(): BelongsTo
    {
        return $this->belongsTo(DonorIdentityCheck::class);
    }

    /** @return BelongsTo<EligibilityRecord, $this> */
    public function eligibilityRecord(): BelongsTo
    {
        return $this->belongsTo(EligibilityRecord::class);
    }

    /** @return BelongsTo<Donation, $this> */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /** @return BelongsTo<User, $this> */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /** @return HasMany<CollectionContainer, $this> */
    public function containers(): HasMany
    {
        return $this->hasMany(CollectionContainer::class);
    }

    /** @return HasMany<Specimen, $this> */
    public function specimens(): HasMany
    {
        return $this->hasMany(Specimen::class);
    }

    /** @return HasMany<CollectionLabel, $this> */
    public function labels(): HasMany
    {
        return $this->hasMany(CollectionLabel::class);
    }

    /** @return HasMany<DonorReaction, $this> */
    public function reactions(): HasMany
    {
        return $this->hasMany(DonorReaction::class);
    }
}
