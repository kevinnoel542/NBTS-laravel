<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class EligibilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $eligibility = $this->resource;
        assert(is_array($eligibility));

        return [
            'status' => $eligibility['status'],
            'eligible' => $eligibility['eligible'],
            'message' => $eligibility['message'],
            'reasons' => $eligibility['reasons'],
            'next_eligible_donation_date' => $eligibility['next_eligible_donation_date'],
            'next_eligible_date' => $eligibility['next_eligible_donation_date'],
            'last_eligibility_checked_at' => $eligibility['last_eligibility_checked_at'],
            'clinical_screening_required' => $eligibility['clinical_screening_required'],
        ];
    }
}
