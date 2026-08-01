<?php

namespace App\Models;

use App\BloodGroup;
use App\BloodUnitStatus;
use Carbon\CarbonImmutable;
use Database\Factories\BloodUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $unit_number
 * @property int $donation_id
 * @property int $donor_id
 * @property int $blood_center_id
 * @property BloodGroup $blood_group
 * @property CarbonImmutable $collection_date
 * @property CarbonImmutable $expiry_date
 * @property BloodUnitStatus $status
 * @property int|null $handled_by
 * @property-read BloodCenter $bloodCenter
 */
#[Fillable([
    'unit_number',
    'donation_id',
    'donor_id',
    'blood_center_id',
    'blood_group',
    'collection_date',
    'expiry_date',
    'status',
    'current_location',
    'handled_by',
])]
class BloodUnit extends Model
{
    /** @use HasFactory<BloodUnitFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'collection_date' => 'date',
            'expiry_date' => 'date',
            'status' => BloodUnitStatus::class,
        ];
    }

    /**
     * @param  Builder<BloodUnit>  $query
     * @return Builder<BloodUnit>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasNationalScope()) {
            return $query;
        }

        return $query->whereIn(
            'blood_center_id',
            $user->centerStaffAssignments()->where('is_active', true)->select('blood_center_id'),
        );
    }

    /** @return BelongsTo<Donation, $this> */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
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
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** @return HasMany<InventoryAdjustment, $this> */
    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }
}
