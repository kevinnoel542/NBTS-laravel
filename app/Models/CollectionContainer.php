<?php

namespace App\Models;

use App\CollectionContainerStatus;
use Database\Factories\CollectionContainerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int $collection_episode_id */
#[Fillable([
    'collection_episode_id',
    'container_identifier',
    'kind',
    'manufacturer_lot',
    'status',
    'quarantine_location',
    'created_by',
    'quarantined_at',
])]
class CollectionContainer extends Model
{
    /** @use HasFactory<CollectionContainerFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quarantined_at' => 'immutable_datetime',
            'status' => CollectionContainerStatus::class,
        ];
    }

    /** @return BelongsTo<CollectionEpisode, $this> */
    public function collectionEpisode(): BelongsTo
    {
        return $this->belongsTo(CollectionEpisode::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function isHardQuarantined(): bool
    {
        return $this->status->isHardQuarantined();
    }
}
