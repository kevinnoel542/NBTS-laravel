<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $totalVolume = $this->relationLoaded('donations')
            ? $this->donations->where('status', 'completed')->sum('volume_ml')
            : $this->donations()->where('status', 'completed')->sum('volume_ml');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'blood_group' => $this->blood_group,
            'gender' => ucfirst($this->gender),
            'region' => $this->region,
            'date_of_birth' => $this->date_of_birth,
            'last_donation' => $this->last_donation,
            'donor_id' => $this->donorProfile?->donor_id,
            'preferred_center' => $this->donorProfile?->preferredCenter?->name,
            'preferred_center_id' => $this->donorProfile?->preferred_center_id,
            'loyalty_tier' => $this->donorProfile?->loyalty_tier,
            'loyalty_points' => $this->donorProfile?->loyalty_points,
            'total_donations' => $this->donorProfile?->total_donations ?? 0,
            'total_volume_ml' => (int) $totalVolume,
            'next_eligible_date' => $this->donorProfile?->next_eligible_donation_date,
            'next_eligible_at' => $this->donorProfile?->next_eligible_donation_date,
            'emergency_contact_name' => $this->donorProfile?->emergency_contact_name,
            'emergency_contact_phone' => $this->donorProfile?->emergency_contact_phone,
            'push_notifications_enabled' => $this->donorProfile?->push_notifications_enabled,
            'sms_reminders_enabled' => $this->donorProfile?->sms_reminders_enabled,
            'share_anonymized_data' => $this->donorProfile?->share_anonymized_data,
            'language' => $this->donorProfile?->language,
            'donor_profile' => new DonorProfileResource($this->whenLoaded('donorProfile')),
        ];
    }
}
