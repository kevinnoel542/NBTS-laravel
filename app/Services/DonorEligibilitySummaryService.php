<?php

namespace App\Services;

use App\DeferralType;
use App\EligibilityStatus;
use App\Models\Deferral;
use App\Models\DonorProfile;
use App\Models\User;
use LogicException;

final class DonorEligibilitySummaryService
{
    /**
     * @return array{
     *     status: string,
     *     eligible: bool,
     *     message: string,
     *     reasons: list<string>,
     *     next_eligible_donation_date: string|null,
     *     last_eligibility_checked_at: string|null,
     *     clinical_screening_required: bool
     * }
     */
    public function forDonor(User $donor): array
    {
        $donor->loadMissing('donorProfile');
        $profile = $donor->donorProfile;

        if (! $profile instanceof DonorProfile) {
            throw new LogicException('A donor profile is required to build an eligibility summary.');
        }

        $activeDeferral = Deferral::query()
            ->where('user_id', $donor->id)
            ->effectiveOn()
            ->orderByRaw("CASE WHEN type = 'permanent' THEN 0 ELSE 1 END")
            ->latest('starts_at')
            ->first();

        if ($activeDeferral !== null) {
            $status = $activeDeferral->type === DeferralType::Permanent
                ? EligibilityStatus::PermanentlyDeferred
                : EligibilityStatus::TemporarilyDeferred;
            $fallbackMessage = $status === EligibilityStatus::PermanentlyDeferred
                ? __('api.eligibility_permanent_deferral')
                : __('api.eligibility_temporary_deferral');
            $reason = trim($activeDeferral->reason) !== ''
                ? $activeDeferral->reason
                : $fallbackMessage;

            return $this->result(
                profile: $profile,
                status: $status,
                reasons: [$reason],
                nextEligibleDate: $activeDeferral->ends_at?->toDateString(),
            );
        }

        if (in_array($profile->eligibility_status, [
            EligibilityStatus::PermanentlyDeferred,
            EligibilityStatus::TemporarilyDeferred,
        ], true)) {
            $fallbackMessage = $profile->eligibility_status === EligibilityStatus::PermanentlyDeferred
                ? __('api.eligibility_permanent_deferral')
                : __('api.eligibility_temporary_deferral');
            $reason = trim((string) $profile->eligibility_notes) !== ''
                ? (string) $profile->eligibility_notes
                : $fallbackMessage;

            return $this->result(
                profile: $profile,
                status: $profile->eligibility_status,
                reasons: [$reason],
                nextEligibleDate: $profile->next_eligible_donation_date?->toDateString(),
            );
        }

        if ($profile->next_eligible_donation_date?->isFuture()) {
            return $this->result(
                profile: $profile,
                status: EligibilityStatus::NotYetEligible,
                reasons: [__('api.eligibility_interval_pending')],
                nextEligibleDate: $profile->next_eligible_donation_date->toDateString(),
            );
        }

        if ($profile->eligibility_status === EligibilityStatus::NotYetEligible) {
            $reason = trim((string) $profile->eligibility_notes) !== ''
                ? (string) $profile->eligibility_notes
                : __('api.eligibility_review_required');

            return $this->result(
                profile: $profile,
                status: EligibilityStatus::NotYetEligible,
                reasons: [$reason],
                nextEligibleDate: $profile->next_eligible_donation_date?->toDateString(),
            );
        }

        return $this->result(
            profile: $profile,
            status: EligibilityStatus::Eligible,
            reasons: [],
            nextEligibleDate: $profile->next_eligible_donation_date?->toDateString(),
        );
    }

    /**
     * @param  list<string>  $reasons
     * @return array{
     *     status: string,
     *     eligible: bool,
     *     message: string,
     *     reasons: list<string>,
     *     next_eligible_donation_date: string|null,
     *     last_eligibility_checked_at: string|null,
     *     clinical_screening_required: bool
     * }
     */
    private function result(
        DonorProfile $profile,
        EligibilityStatus $status,
        array $reasons,
        ?string $nextEligibleDate,
    ): array {
        $message = $reasons === []
            ? __('api.eligibility_currently_eligible')
            : implode(' ', $reasons);

        return [
            'status' => $status->value,
            'eligible' => $status->allowsDonation(),
            'message' => $message,
            'reasons' => $reasons,
            'next_eligible_donation_date' => $nextEligibleDate,
            'last_eligibility_checked_at' => $profile->last_eligibility_checked_at?->toIso8601String(),
            'clinical_screening_required' => true,
        ];
    }
}
