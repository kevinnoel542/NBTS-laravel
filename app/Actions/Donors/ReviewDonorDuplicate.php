<?php

namespace App\Actions\Donors;

use App\DonationStatus;
use App\DonorDuplicateCaseStatus;
use App\Models\DonorDuplicateCase;
use App\Models\DonorIdentityAlias;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ReviewDonorDuplicate
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, DonorDuplicateCase $duplicateCase, DonorDuplicateCaseStatus $decision, string $reason): DonorDuplicateCase
    {
        Gate::forUser($actor)->authorize('review', $duplicateCase);

        if (! in_array($decision, [DonorDuplicateCaseStatus::Merged, DonorDuplicateCaseStatus::Rejected], true)) {
            throw ValidationException::withMessages(['decision' => ['Select merge or reject.']]);
        }

        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages(['reason' => ['A review reason of at least 10 characters is required.']]);
        }

        return DB::transaction(function () use ($actor, $duplicateCase, $decision, $reason): DonorDuplicateCase {
            $case = DonorDuplicateCase::query()->lockForUpdate()->findOrFail($duplicateCase->id);

            if ($case->status !== DonorDuplicateCaseStatus::Pending) {
                throw ValidationException::withMessages(['decision' => ['This duplicate case was already reviewed.']]);
            }

            if ($decision === DonorDuplicateCaseStatus::Merged) {
                $this->merge($actor, $case, $reason);
            }

            $case->forceFill([
                'status' => $decision,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'review_reason' => trim($reason),
            ])->save();

            foreach ([$case->primary_donor_id, $case->candidate_donor_id] as $donorId) {
                $hasPending = DonorDuplicateCase::query()->pending()->where(fn ($query) => $query
                    ->where('primary_donor_id', $donorId)
                    ->orWhere('candidate_donor_id', $donorId))->exists();
                DB::table('donor_profiles')->where('user_id', $donorId)->update(['identity_review_required' => $hasPending]);
            }

            $this->auditLogger->record(
                actor: $actor,
                action: 'donor.duplicate_'.$decision->value,
                subject: $case,
                bloodCenter: $case->bloodCenter,
                metadata: ['primary_donor_id' => $case->primary_donor_id, 'candidate_donor_id' => $case->candidate_donor_id],
            );

            return $case->fresh(['primaryDonor.donorProfile', 'candidateDonor.donorProfile', 'identityAlias']);
        }, attempts: 3);
    }

    private function merge(User $actor, DonorDuplicateCase $case, string $reason): void
    {
        $canonical = User::query()->lockForUpdate()->findOrFail($case->primary_donor_id);
        $source = User::query()->lockForUpdate()->findOrFail($case->candidate_donor_id);
        $sourceIdentifier = (string) $source->donorProfile?->donor_id;

        foreach (['appointments', 'donations', 'eligibility_records', 'deferrals'] as $table) {
            DB::table($table)->where('user_id', $source->id)->update(['user_id' => $canonical->id]);
        }

        DB::table('blood_units')->where('donor_id', $source->id)->update(['donor_id' => $canonical->id]);
        DB::table('collection_episodes')->where('donor_id', $source->id)->update(['donor_id' => $canonical->id]);
        DB::table('donor_identity_checks')->where('donor_id', $source->id)->update(['donor_id' => $canonical->id]);
        DB::table('donor_reactions')->where('donor_id', $source->id)->update(['donor_id' => $canonical->id]);

        DonorIdentityAlias::query()->create([
            'canonical_donor_id' => $canonical->id,
            'source_donor_id' => $source->id,
            'duplicate_case_id' => $case->id,
            'source_donor_identifier' => $sourceIdentifier,
            'merged_by' => $actor->id,
            'reason' => trim($reason),
            'merged_at' => now(),
        ]);

        $source->forceFill([
            'email' => null,
            'phone' => null,
            'is_active' => false,
        ])->save();
        $source->syncRoles([]);

        $canonical->donorProfile?->forceFill([
            'total_donations' => $canonical->donations()->where('status', DonationStatus::Completed)->count(),
        ])->save();
    }
}
