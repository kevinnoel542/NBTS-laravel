<?php

namespace App\Models;

use App\CollectionLabelStatus;
use Database\Factories\CollectionLabelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CollectionLabelStatus $status
 * @property int $print_count
 */
#[Fillable([
    'collection_episode_id',
    'collection_container_id',
    'specimen_id',
    'label_identifier',
    'symbology',
    'template_version',
    'status',
    'print_count',
    'printer_name',
    'printed_by',
    'printed_at',
    'applied_by',
    'applied_at',
    'voided_by',
    'voided_at',
    'reason',
])]
class CollectionLabel extends Model
{
    /** @use HasFactory<CollectionLabelFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'applied_at' => 'immutable_datetime',
            'print_count' => 'integer',
            'printed_at' => 'immutable_datetime',
            'status' => CollectionLabelStatus::class,
            'voided_at' => 'immutable_datetime',
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

    /** @return BelongsTo<Specimen, $this> */
    public function specimen(): BelongsTo
    {
        return $this->belongsTo(Specimen::class);
    }

    /** @return BelongsTo<User, $this> */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function applier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    /** @return BelongsTo<User, $this> */
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
