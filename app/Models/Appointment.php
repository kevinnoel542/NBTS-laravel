<?php

namespace App\Models;

use App\AppointmentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property int $blood_center_id
 * @property CarbonImmutable $scheduled_at
 * @property AppointmentStatus $status
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable|null $rescheduled_at
 * @property int|null $handled_by
 * @property string|null $notes
 * @property-read BloodCenter $bloodCenter
 */
#[Fillable([
    'user_id',
    'blood_center_id',
    'scheduled_at',
    'status',
    'confirmed_at',
    'cancelled_at',
    'rescheduled_at',
    'handled_by',
    'notes',
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }

    /**
     * @param  Builder<Appointment>  $query
     * @return Builder<Appointment>
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
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** @return HasOne<Donation, $this> */
    public function donation(): HasOne
    {
        return $this->hasOne(Donation::class);
    }
}
