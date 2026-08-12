<?php

namespace App\Models;

use App\Services\ActiveAssignmentContext;
use Database\Factories\BloodCenterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property string|null $collection_identifier_prefix
 * @property int|null $daily_collection_capacity
 * @property bool $offline_collection_enabled
 * @property string|null $image_path
 * @property string|null $latitude
 * @property string|null $longitude
 * @property bool $is_active
 */
#[Fillable([
    'organization_unit_id',
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
    'collection_identifier_prefix',
    'daily_collection_capacity',
    'offline_collection_enabled',
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
            'daily_collection_capacity' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'offline_collection_enabled' => 'boolean',
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

        $assignment = app(ActiveAssignmentContext::class)->selectedAssignment($user);

        if ($assignment instanceof StaffAssignment) {
            $bloodCenterId = $assignment->organizationUnit->bloodCenter?->id;

            return $bloodCenterId === null
                ? $query->whereRaw('1 = 0')
                : $query->whereKey($bloodCenterId);
        }

        return $query->whereHas('staffAssignments', function (Builder $assignmentQuery) use ($user): void {
            $assignmentQuery
                ->where('user_id', $user->id)
                ->where('is_active', true);
        });
    }

    /** @return BelongsTo<OrganizationUnit, $this> */
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    /** @return HasMany<StaffAssignment, $this> */
    public function operationalAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class, 'organization_unit_id', 'organization_unit_id');
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

    /** @return HasMany<Campaign, $this> */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
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

    /** @return HasMany<AuditLog, $this> */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /** @return HasMany<CollectionEpisode, $this> */
    public function collectionEpisodes(): HasMany
    {
        return $this->hasMany(CollectionEpisode::class);
    }

    /** @return HasMany<OfflineCollectionDevice, $this> */
    public function offlineCollectionDevices(): HasMany
    {
        return $this->hasMany(OfflineCollectionDevice::class);
    }
}
