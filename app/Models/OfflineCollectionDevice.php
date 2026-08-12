<?php

namespace App\Models;

use App\OfflineCollectionDeviceStatus;
use Carbon\CarbonImmutable;
use Database\Factories\OfflineCollectionDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property OfflineCollectionDeviceStatus $status
 * @property int $blood_center_id
 * @property CarbonImmutable|null $last_synced_at
 * @property CarbonImmutable|null $revoked_at
 */
#[Fillable([
    'device_uuid',
    'blood_center_id',
    'assigned_to',
    'name',
    'status',
    'credential_fingerprint',
    'last_synced_at',
    'revoked_at',
    'revoked_by',
    'revocation_reason',
])]
class OfflineCollectionDevice extends Model
{
    /** @use HasFactory<OfflineCollectionDeviceFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'status' => OfflineCollectionDeviceStatus::class,
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return BelongsTo<User, $this> */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /** @return HasMany<OfflineIdentifierBatch, $this> */
    public function identifierBatches(): HasMany
    {
        return $this->hasMany(OfflineIdentifierBatch::class);
    }

    /** @return HasMany<OfflineCollectionSubmission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(OfflineCollectionSubmission::class);
    }
}
