<?php

namespace App\Models;

use App\LaboratorySpecimenReceiptStatus;
use Database\Factories\LaboratorySpecimenReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'specimen_id',
    'collection_episode_id',
    'collection_container_id',
    'blood_center_id',
    'received_by',
    'scanned_identifier',
    'receiving_station',
    'status',
    'received_at',
    'rejection_reason',
    'exception_notes',
])]
class LaboratorySpecimenReceipt extends Model
{
    /** @use HasFactory<LaboratorySpecimenReceiptFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'received_at' => 'immutable_datetime',
            'status' => LaboratorySpecimenReceiptStatus::class,
        ];
    }

    /** @return BelongsTo<Specimen, $this> */
    public function specimen(): BelongsTo
    {
        return $this->belongsTo(Specimen::class);
    }

    /** @return BelongsTo<CollectionEpisode, $this> */
    public function collectionEpisode(): BelongsTo
    {
        return $this->belongsTo(CollectionEpisode::class);
    }

    /** @return BelongsTo<CollectionContainer, $this> */
    public function collectionContainer(): BelongsTo
    {
        return $this->belongsTo(CollectionContainer::class);
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** @return HasMany<LaboratoryTestOrder, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(LaboratoryTestOrder::class);
    }
}
