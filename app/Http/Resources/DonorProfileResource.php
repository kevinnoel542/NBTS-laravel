<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'preferred_center_id' => $this->preferred_center_id,
            'preferred_center' => $this->preferredCenter?->name,
            'donor_id' => $this->donor_id,
            'blood_group_status' => $this->blood_group_status,
            'blood_group_verified' => (bool) $this->blood_group_verified,
            'blood_group_verified_at' => $this->blood_group_verified_at,
            'next_eligible_donation_date' => $this->next_eligible_donation_date,
            'eligibility_status' => $this->eligibility_status,
            'last_eligibility_checked_at' => $this->last_eligibility_checked_at,
            'eligibility_notes' => $this->eligibility_notes,
            'total_donations' => $this->total_donations,
            'loyalty_points' => $this->loyalty_points,
            'loyalty_tier' => $this->loyalty_tier,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'push_notifications_enabled' => $this->push_notifications_enabled,
            'sms_reminders_enabled' => $this->sms_reminders_enabled,
            'share_anonymized_data' => $this->share_anonymized_data,
            'language' => $this->language,
        ];
    }
}
