<?php

namespace App\Models;

use Database\Factories\BloodCenterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $city
 * @property string $phone
 * @property string $email
 * @property string|null $opening_hours
 * @property array<int, string>|null $services
 * @property string|null $capacity_label
 * @property int|null $estimated_wait_minutes
 * @property string|null $center_type
 * @property string|null $image_path
 * @property string|null $latitude
 * @property string|null $longitude
 * @property bool $is_active
 */
#[Fillable([
    'name',
    'address',
    'city',
    'phone',
    'email',
    'opening_hours',
    'services',
    'capacity_label',
    'estimated_wait_minutes',
    'center_type',
    'image_path',
    'latitude',
    'longitude',
    'is_active',
])]
class BloodCenter extends Model
{
    /** @use HasFactory<BloodCenterFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'services' => 'array',
        ];
    }

    /**
     * @param  Builder<BloodCenter>  $query
     * @return Builder<BloodCenter>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Limit operational records to centers available to the staff account.
     *
     * @param  Builder<BloodCenter>  $query
     * @return Builder<BloodCenter>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasNationalScope()) {
            return $query;
        }

        return $query->whereHas('staffAssignments', function (Builder $assignmentQuery) use ($user): void {
            $assignmentQuery
                ->where('user_id', $user->id)
                ->where('is_active', true);
        });
    }

    /** @return HasMany<CenterStaff, $this> */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(CenterStaff::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function staffMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'center_staff')
            ->withPivot(['position', 'is_active'])
            ->withTimestamps();
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** @return HasMany<Donation, $this> */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /** @return HasMany<BloodUnit, $this> */
    public function bloodUnits(): HasMany
    {
        return $this->hasMany(BloodUnit::class);
    }

    /** @return HasMany<BloodInventory, $this> */
    public function inventory(): HasMany
    {
        return $this->hasMany(BloodInventory::class);
    }

    /** @return HasMany<InventoryAdjustment, $this> */
    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /** @return HasMany<LowStockAlert, $this> */
    public function lowStockAlerts(): HasMany
    {
        return $this->hasMany(LowStockAlert::class);
    }
}
