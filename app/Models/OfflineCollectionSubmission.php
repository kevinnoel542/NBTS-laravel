<?php

namespace App\Models;

use App\OfflineCollectionSubmissionStatus;
use App\Services\ActiveAssignmentContext;
use Database\Factories\OfflineCollectionSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property OfflineCollectionSubmissionStatus $status
 * @property array<string, mixed> $payload
 * @property list<string>|null $conflict_codes
 */
#[Fillable([
    'client_submission_id',
    'offline_collection_device_id',
    'offline_identifier_batch_id',
    'blood_center_id',
    'submitted_by',
    'donation_identifier',
    'payload_hash',
    'payload',
    'status',
    'conflict_codes',
    'collection_episode_id',
    'reviewed_by',
    'received_at',
    'reconciled_at',
    'reviewed_at',
    'review_reason',
])]
class OfflineCollectionSubmission extends Model
{
    /** @use HasFactory<OfflineCollectionSubmissionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'conflict_codes' => 'array',
            'payload' => 'encrypted:array',
            'received_at' => 'immutable_datetime',
            'reconciled_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'status' => OfflineCollectionSubmissionStatus::class,
        ];
    }

    /**
     * @param  Builder<OfflineCollectionSubmission>  $query
     * @return Builder<OfflineCollectionSubmission>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasNationalScope()) {
            return $query;
        }

        $assignment = app(ActiveAssignmentContext::class)->selectedAssignment($user);
        $selectedCenterId = $assignment?->organizationUnit->bloodCenter?->id;

        return $selectedCenterId === null
            ? $query->whereRaw('1 = 0')
            : $query->where('blood_center_id', $selectedCenterId);
    }

    /** @return BelongsTo<OfflineCollectionDevice, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(OfflineCollectionDevice::class, 'offline_collection_device_id');
    }

    /** @return BelongsTo<OfflineIdentifierBatch, $this> */
    public function identifierBatch(): BelongsTo
    {
        return $this->belongsTo(OfflineIdentifierBatch::class, 'offline_identifier_batch_id');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<CollectionEpisode, $this> */
    public function collectionEpisode(): BelongsTo
    {
        return $this->belongsTo(CollectionEpisode::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
