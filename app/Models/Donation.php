<?php

namespace App\Models;

use App\BloodGroup;
use App\DonationStatus;
use App\DonationType;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'blood_center_id',
    'recorded_by',
    'appointment_id',
    'donation_type',
    'blood_group',
    'blood_group_verified',
    'volume_ml',
    'donation_date',
    'status',
    'notes',
])]
class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'blood_group_verified' => 'boolean',
            'donation_date' => 'date',
            'donation_type' => DonationType::class,
            'status' => DonationStatus::class,
        ];
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
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

    /** @return BelongsTo<User, $this> */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return HasOne<BloodUnit, $this> */
    public function bloodUnit(): HasOne
    {
        return $this->hasOne(BloodUnit::class);
    }
}
