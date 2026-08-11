<?php

namespace App\Actions\Eligibility;

use App\DeferralType;
use App\EligibilityStatus;
use App\Models\Deferral;
use App\Models\DonorProfile;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class LiftDeferral
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(Deferral $deferral, User $actor, string $reason): Deferral
    {
        $reason = trim($reason);

        if (mb_strlen($reason) < 10) {
            throw ValidationException::withMessages([
                'deferralLiftReason' => [__('console.workflow.reason_required')],
            ]);
        }

        return DB::transaction(function () use ($deferral, $actor, $reason): Deferral {
            $lockedDeferral = Deferral::query()
                ->lockForUpdate()
                ->findOrFail($deferral->id);

            Gate::forUser($actor)->authorize('update', $lockedDeferral);

            if (! $lockedDeferral->is_active) {
                throw ValidationException::withMessages([
                    'deferralLiftReason' => [__('console.workflow.deferral_already_lifted')],
                ]);
            }

            $profile = DonorProfile::query()
                ->with('preferredCenter')
                ->where('user_id', $lockedDeferral->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedDeferral->forceFill([
                'is_active' => false,
                'lifted_at' => now(),
                'lifted_by' => $actor->id,
            ])->save();

            $remainingDeferral = Deferral::query()
                ->where('user_id', $lockedDeferral->user_id)
                ->whereKeyNot($lockedDeferral->id)
                ->effectiveOn()
                ->orderByRaw(
                    'CASE WHEN type = ? THEN 0 ELSE 1 END',
                    [DeferralType::Permanent->value],
                )
                ->lockForUpdate()
                ->first();

            $profile->forceFill([
                'eligibility_notes' => $remainingDeferral->reason
                    ?? __('console.workflow.deferral_lifted_screening_required'),
                'eligibility_status' => match ($remainingDeferral?->type) {
                    DeferralType::Permanent => EligibilityStatus::PermanentlyDeferred,
                    DeferralType::Temporary => EligibilityStatus::TemporarilyDeferred,
                    null => EligibilityStatus::NotYetEligible,
                },
                'last_eligibility_checked_at' => now(),
                'next_eligible_donation_date' => $remainingDeferral->ends_at
                    ?? ($profile->next_eligible_donation_date?->isFuture()
                        ? $profile->next_eligible_donation_date
                        : null),
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'eligibility.deferral_lifted',
                subject: $lockedDeferral,
                bloodCenter: $profile->preferredCenter,
                metadata: [
                    'reason' => $reason,
                    'remaining_active_deferral_id' => $remainingDeferral?->id,
                    'resulting_status' => $profile->eligibility_status->value,
                ],
            );

            return $lockedDeferral->refresh()->load(['donor', 'lifter']);
        }, attempts: 3);
    }
}
