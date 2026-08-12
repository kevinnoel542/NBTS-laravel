<?php

namespace App\Models;

use App\DonorIdentityCheckStatus;
use App\DonorIdentityMethod;
use Carbon\CarbonImmutable;
use Database\Factories\DonorIdentityCheckFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property DonorIdentityMethod $method
 * @property DonorIdentityCheckStatus $status
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $expires_at
 */
#[Fillable([
    'donor_id',
    'blood_center_id',
    'appointment_id',
    'method',
    'reference_suffix',
    'status',
    'confirmed_by',
    'confirmed_at',
    'expires_at',
    'source_mode',
    'failure_reason',
])]
class DonorIdentityCheck extends Model
{
    /** @use HasFactory<DonorIdentityCheckFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'confirmed_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'method' => DonorIdentityMethod::class,
            'status' => DonorIdentityCheckStatus::class,
        ];
    }

    /**
     * @param  Builder<DonorIdentityCheck>  $query
     * @return Builder<DonorIdentityCheck>
     */
    public function scopeEffective(Builder $query): Builder
    {
        return $query
            ->where('status', DonorIdentityCheckStatus::Confirmed)
            ->whereNotNull('confirmed_at')
            ->where(function (Builder $effectiveQuery): void {
                $effectiveQuery->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
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

    /** @return BelongsTo<User, $this> */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /** @return HasMany<CollectionEpisode, $this> */
    public function collectionEpisodes(): HasMany
    {
        return $this->hasMany(CollectionEpisode::class, 'identity_check_id');
    }
}
