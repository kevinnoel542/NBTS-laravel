<?php

namespace App\Services;

use App\EligibilityStatus;
use App\Models\Deferral;
use App\Models\DonorDuplicateCase;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\User;
use App\RoleName;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class DonorEligibilityService
{
    /**
     * @throws ValidationException
     */
    public function assertEligibleForDonation(User $donor, CarbonImmutable $donationDate): DonorProfile
    {
        if (! $donor->is_active || ! $donor->hasRole(RoleName::Donor->value)) {
            throw ValidationException::withMessages([
                'donor' => ['An active donor account is required.'],
            ]);
        }

        $profile = DonorProfile::query()
            ->where('user_id', $donor->id)
            ->lockForUpdate()
            ->first();

        if (! $profile) {
            throw ValidationException::withMessages([
                'donor' => ['The donor profile must be completed before donation.'],
            ]);
        }

        if ($profile->identity_review_required
            || DonorDuplicateCase::query()->pending()->where(fn ($query) => $query
                ->where('primary_donor_id', $donor->id)
                ->orWhere('candidate_donor_id', $donor->id))->exists()) {
            throw ValidationException::withMessages([
                'identity' => ['A pending duplicate identity review blocks collection.'],
            ]);
        }

        if ($profile->eligibility_status !== EligibilityStatus::Eligible) {
            throw ValidationException::withMessages([
                'eligibility' => ['The donor profile is not currently marked eligible.'],
            ]);
        }

        if ($profile->next_eligible_donation_date?->isAfter($donationDate)) {
            throw ValidationException::withMessages([
                'eligibility' => ['The minimum interval since the previous donation has not elapsed.'],
            ]);
        }

        $hasActiveDeferral = Deferral::query()
            ->where('user_id', $donor->id)
            ->effectiveOn($donationDate)
            ->exists();

        if ($hasActiveDeferral) {
            throw ValidationException::withMessages([
                'eligibility' => ['The donor has an active deferral.'],
            ]);
        }

        $hasEligibleScreening = EligibilityRecord::query()
            ->where('user_id', $donor->id)
            ->where('status', EligibilityStatus::Eligible)
            ->where(fn ($query) => $query
                ->whereDate('screened_at', $donationDate->toDateString())
                ->orWhere(fn ($legacyQuery) => $legacyQuery
                    ->whereNull('screened_at')
                    ->whereDate('created_at', $donationDate->toDateString())))
            ->exists();

        if (! $hasEligibleScreening) {
            throw ValidationException::withMessages([
                'eligibility' => ['An eligible screening recorded on the donation date is required.'],
            ]);
        }

        return $profile;
    }
}
