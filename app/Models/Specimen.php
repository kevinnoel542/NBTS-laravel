<?php

namespace App\Models;

use App\SpecimenStatus;
use Database\Factories\SpecimenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property SpecimenStatus $status
 * @property bool $is_required
 * @property string|null $volume_ml
 */
#[Fillable([
    'collection_episode_id',
    'collection_container_id',
    'specimen_identifier',
    'specimen_type',
    'status',
    'is_required',
    'volume_ml',
    'collected_by',
    'collected_at',
    'handed_off_by',
    'handed_off_at',
    'handoff_recipient',
    'rejection_reason',
])]
class Specimen extends Model
{
    /** @use HasFactory<SpecimenFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'collected_at' => 'immutable_datetime',
            'handed_off_at' => 'immutable_datetime',
            'is_required' => 'boolean',
            'status' => SpecimenStatus::class,
            'volume_ml' => 'decimal:2',
        ];
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

    /** @return BelongsTo<User, $this> */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /** @return BelongsTo<User, $this> */
    public function handoffActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_off_by');
    }

    /** @return HasMany<CollectionLabel, $this> */
    public function labels(): HasMany
    {
        return $this->hasMany(CollectionLabel::class);
    }
}
