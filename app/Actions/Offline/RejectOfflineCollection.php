<?php

namespace App\Actions\Offline;

use App\Models\OfflineCollectionSubmission;
use App\Models\User;
use App\OfflineCollectionSubmissionStatus;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class RejectOfflineCollection
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, OfflineCollectionSubmission $submission, string $reason): OfflineCollectionSubmission
    {
        Gate::forUser($actor)->authorize('reconcile', $submission);
        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages(['reason' => ['A rejection reason of at least 10 characters is required.']]);
        }

        return DB::transaction(function () use ($actor, $submission, $reason): OfflineCollectionSubmission {
            $record = OfflineCollectionSubmission::query()->with('bloodCenter')->lockForUpdate()->findOrFail($submission->id);
            if ($record->status === OfflineCollectionSubmissionStatus::Reconciled) {
                throw ValidationException::withMessages(['submission' => ['A reconciled submission cannot be rejected.']]);
            }
            $record->forceFill([
                'status' => OfflineCollectionSubmissionStatus::Rejected,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'review_reason' => trim($reason),
            ])->save();
            $this->auditLogger->record($actor, 'offline.collection_rejected', $record, $record->bloodCenter, ['reason' => trim($reason)]);

            return $record;
        }, attempts: 3);
    }
}
