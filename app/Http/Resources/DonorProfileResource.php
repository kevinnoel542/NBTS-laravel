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
            'preferred_center_name' => $this->preferredCenter?->name,
            'preferred_center_address' => $this->preferredCenter?->address,
            'donor_id' => $this->donor_id,
            'blood_group_status' => $this->blood_group_status,
            'blood_group_status_label' => str($this->blood_group_status)->replace('_', ' ')->title()->toString(),
            'blood_group_verified' => (bool) $this->blood_group_verified,
            'blood_group_verified_at' => $this->blood_group_verified_at,
            'blood_group_verified_at_label' => optional($this->blood_group_verified_at)->format('M d, Y h:i A'),
            'next_eligible_donation_date' => $this->next_eligible_donation_date,
            'next_eligible_donation_date_label' => optional($this->next_eligible_donation_date)->format('M d, Y'),
            'is_eligible_now' => ! $this->next_eligible_donation_date || $this->next_eligible_donation_date->isPast() || $this->next_eligible_donation_date->isToday(),
            'eligibility_status' => $this->eligibility_status,
            'eligibility_status_label' => str($this->eligibility_status ?: 'unknown')->replace('_', ' ')->title()->toString(),
            'last_eligibility_checked_at' => $this->last_eligibility_checked_at,
            'last_eligibility_checked_at_label' => optional($this->last_eligibility_checked_at)->format('M d, Y h:i A'),
            'eligibility_notes' => $this->eligibility_notes,
            'total_donations' => (int) $this->total_donations,
            'loyalty_points' => (int) $this->loyalty_points,
            'loyalty_tier' => $this->loyalty_tier,
            'loyalty_tier_label' => str($this->loyalty_tier ?: 'bronze')->title()->toString(),
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'push_notifications_enabled' => (bool) $this->push_notifications_enabled,
            'sms_reminders_enabled' => (bool) $this->sms_reminders_enabled,
            'share_anonymized_data' => (bool) $this->share_anonymized_data,
            'language' => $this->language,
            'language_label' => $this->language === 'sw' ? 'Swahili' : 'English',
        ];
    }
}
