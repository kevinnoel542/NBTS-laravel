<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\OfflineIdentifierBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $year
 * @property int $offline_collection_device_id
 * @property int $blood_center_id
 * @property int $start_sequence
 * @property int $end_sequence
 * @property int $next_sequence
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $revoked_at
 */
#[Fillable([
    'offline_collection_device_id',
    'blood_center_id',
    'year',
    'prefix',
    'start_sequence',
    'end_sequence',
    'next_sequence',
    'issued_by',
    'issued_at',
    'expires_at',
    'revoked_at',
])]
class OfflineIdentifierBatch extends Model
{
    /** @use HasFactory<OfflineIdentifierBatchFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'end_sequence' => 'integer',
            'expires_at' => 'immutable_datetime',
            'issued_at' => 'immutable_datetime',
            'next_sequence' => 'integer',
            'revoked_at' => 'immutable_datetime',
            'start_sequence' => 'integer',
            'year' => 'integer',
        ];
    }

    /** @return BelongsTo<OfflineCollectionDevice, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(OfflineCollectionDevice::class, 'offline_collection_device_id');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** @return HasMany<OfflineCollectionSubmission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(OfflineCollectionSubmission::class);
    }
}
