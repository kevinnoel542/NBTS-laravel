<?php

namespace App\Actions\Donations;

use App\BloodGroup;
use App\BloodGroupStatus;
use App\BloodUnitStatus;
use App\Models\Donation;
use App\Models\User;
use App\PermissionName;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VerifyBloodGroup
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(Donation $donation, BloodGroup $bloodGroup, User $actor, ?string $reason = null): Donation
    {
        return DB::transaction(function () use ($donation, $bloodGroup, $actor, $reason): Donation {
            $lockedDonation = Donation::query()
                ->with(['bloodUnit', 'bloodCenter', 'donor.donorProfile'])
                ->lockForUpdate()
                ->findOrFail($donation->id);
            $profile = $lockedDonation->donor->donorProfile;

            if ($profile === null) {
                throw ValidationException::withMessages([
                    'verificationBloodGroup' => [__('console.workflow.donor_profile_required')],
                ]);
            }

            Gate::forUser($actor)->authorize('verifyBloodGroup', $profile);

            $previousGroup = $lockedDonation->blood_group;
            $isCorrection = $lockedDonation->blood_group_verified && $previousGroup !== $bloodGroup;

            if ($isCorrection && (! $actor->can(PermissionName::ManageDonors->value) || mb_strlen(trim((string) $reason)) < 10)) {
                throw ValidationException::withMessages([
                    'verificationReason' => [__('console.workflow.correction_reason_required')],
                ]);
            }

            if ($lockedDonation->bloodUnit !== null
                && ! in_array($lockedDonation->bloodUnit->status, [BloodUnitStatus::Collected, BloodUnitStatus::Testing], true)
                && $lockedDonation->bloodUnit->blood_group !== $bloodGroup) {
                throw ValidationException::withMessages([
                    'verificationBloodGroup' => [__('console.workflow.inventory_group_locked')],
                ]);
            }

            $lockedDonation->forceFill([
                'blood_group' => $bloodGroup,
                'blood_group_verified' => true,
            ])->save();

            $lockedDonation->donor->forceFill(['blood_group' => $bloodGroup])->save();
            $profile->forceFill([
                'blood_group_status' => BloodGroupStatus::StaffVerified,
                'blood_group_verified' => true,
                'blood_group_verified_at' => now(),
                'blood_group_verified_by' => $actor->id,
            ])->save();

            if ($lockedDonation->bloodUnit !== null) {
                $lockedDonation->bloodUnit->forceFill(['blood_group' => $bloodGroup])->save();
            }

            $this->auditLogger->record(
                actor: $actor,
                action: $isCorrection ? 'donations.blood_group_corrected' : 'donations.blood_group_verified',
                subject: $lockedDonation,
                bloodCenter: $lockedDonation->bloodCenter,
                metadata: [
                    'from_blood_group' => $previousGroup->value,
                    'reason_provided' => filled($reason),
                    'to_blood_group' => $bloodGroup->value,
                ],
            );

            return $lockedDonation->refresh()->load(['bloodUnit', 'donor.donorProfile']);
        }, attempts: 3);
    }
}
