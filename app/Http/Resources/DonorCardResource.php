<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;

        return [
            'donor_id' => $this->donor_id,
            'qr_payload' => [
                'type' => 'nbts_donor_card',
                'version' => 1,
                'donor_id' => $this->donor_id,
                'issued_at' => now()->toISOString(),
            ],
            'donor' => [
                'name' => $user?->name,
                'phone' => $user?->phone,
                'blood_group' => $user?->blood_group,
                'blood_group_verified' => (bool) $this->blood_group_verified,
                'region' => $user?->region,
                'preferred_center' => $this->preferredCenter?->name,
            ],
            'stats' => [
                'total_donations' => $this->total_donations,
                'last_donation' => $user?->last_donation,
                'next_eligible_donation_date' => $this->next_eligible_donation_date,
                'eligibility_status' => $this->eligibility_status,
                'loyalty_points' => $this->loyalty_points,
                'loyalty_tier' => $this->loyalty_tier,
            ],
            'qr_expires_at' => now()->addDay()->toISOString(),
        ];
    }
}
