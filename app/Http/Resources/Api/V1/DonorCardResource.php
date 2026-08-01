<?php

namespace App\Http\Resources\Api\V1;

use App\Models\DonorProfile;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DonorCardResource extends JsonResource
{
    /**
     * @param  array{
     *     status: string,
     *     eligible: bool,
     *     message: string,
     *     reasons: list<string>,
     *     next_eligible_donation_date: string|null,
     *     last_eligibility_checked_at: string|null,
     *     clinical_screening_required: bool
     * }  $eligibilitySummary
     * @param  array{
     *     total_donations: int,
     *     total_volume_ml: int,
     *     total_volume_liters: float,
     *     last_donation: string|null,
     *     lives_touched: int,
     *     lives_touched_is_estimate: bool
     * }  $donationSummary
     */
    public function __construct(
        DonorProfile $resource,
        private readonly string $qrPayload,
        private readonly CarbonImmutable $qrExpiresAt,
        private readonly array $eligibilitySummary,
        private readonly array $donationSummary,
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->resource;
        assert($profile instanceof DonorProfile);
        $user = $profile->user;
        $preferredCenterName = $profile->preferredCenter?->name;

        return [
            'donor_id' => $profile->donor_id,
            'qr_payload' => $this->qrPayload,
            'qr_expires_at' => $this->qrExpiresAt->toIso8601String(),
            'name' => $user->name,
            'phone' => $user->phone,
            'blood_group' => $user->blood_group?->value,
            'donor' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'blood_group' => $user->blood_group?->value,
                'blood_group_status' => $profile->blood_group_status->value,
                'blood_group_verified' => $profile->blood_group_verified,
                'region' => $user->region,
                'preferred_center' => $preferredCenterName,
            ],
            'stats' => [
                'total_donations' => $this->donationSummary['total_donations'],
                'total_volume_ml' => $this->donationSummary['total_volume_ml'],
                'total_volume_liters' => $this->donationSummary['total_volume_liters'],
                'last_donation' => $this->donationSummary['last_donation'],
                'next_eligible_donation_date' => $this->eligibilitySummary['next_eligible_donation_date'],
                'eligibility_status' => $this->eligibilitySummary['status'],
                'eligibility_status_label' => __('operations.status.'.$this->eligibilitySummary['status']),
                'loyalty_points' => $profile->loyalty_points,
                'loyalty_tier' => $profile->loyalty_tier,
            ],
        ];
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode(200);
    }
}
