<?php

namespace App\Models;

use App\BloodGroup;
use App\ComponentStatus;
use Database\Factories\BloodComponentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property BloodGroup $blood_group
 * @property ComponentStatus $status
 * @property Carbon $expiry_date
 */
#[Fillable([
    'product_identifier',
    'blood_unit_id',
    'donation_id',
    'parent_component_id',
    'component_product_catalog_id',
    'component_processing_event_id',
    'blood_center_id',
    'blood_group',
    'status',
    'storage_location',
    'cold_chain_device_id',
    'special_attributes',
    'expiry_date',
    'processed_at',
    'released_at',
    'reserved_at',
    'allocated_at',
    'issued_at',
    'dispatched_at',
    'received_at',
    'returned_at',
    'recalled_at',
    'investigation_hold_at',
    'expired_at',
    'disposed_at',
])]
class BloodComponent extends Model
{
    /** @use HasFactory<BloodComponentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'allocated_at' => 'immutable_datetime',
            'blood_group' => BloodGroup::class,
            'dispatched_at' => 'immutable_datetime',
            'disposed_at' => 'immutable_datetime',
            'expired_at' => 'immutable_datetime',
            'expiry_date' => 'date',
            'investigation_hold_at' => 'immutable_datetime',
            'issued_at' => 'immutable_datetime',
            'processed_at' => 'immutable_datetime',
            'recalled_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
            'released_at' => 'immutable_datetime',
            'reserved_at' => 'immutable_datetime',
            'returned_at' => 'immutable_datetime',
            'special_attributes' => 'array',
            'status' => ComponentStatus::class,
        ];
    }

    /** @return BelongsTo<BloodUnit, $this> */
    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    /** @return BelongsTo<Donation, $this> */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function parentComponent(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'parent_component_id');
    }

    /** @return HasMany<BloodComponent, $this> */
    public function childComponents(): HasMany
    {
        return $this->hasMany(BloodComponent::class, 'parent_component_id');
    }

    /** @return BelongsTo<ComponentProductCatalog, $this> */
    public function productCatalog(): BelongsTo
    {
        return $this->belongsTo(ComponentProductCatalog::class, 'component_product_catalog_id');
    }

    /** @return BelongsTo<ComponentProcessingEvent, $this> */
    public function processingEvent(): BelongsTo
    {
        return $this->belongsTo(ComponentProcessingEvent::class, 'component_processing_event_id');
    }

    /** @return HasMany<HospitalComponentAllocation, $this> */
    public function hospitalAllocations(): HasMany
    {
        return $this->hasMany(HospitalComponentAllocation::class, 'blood_component_id');
    }

    /** @return HasMany<TransfusionRecord, $this> */
    public function transfusionRecords(): HasMany
    {
        return $this->hasMany(TransfusionRecord::class, 'blood_component_id');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function isAvailableForAllocation(): bool
    {
        return $this->status === ComponentStatus::Available && $this->expiry_date->isFuture();
    }
}
