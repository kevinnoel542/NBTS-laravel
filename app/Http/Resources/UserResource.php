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
        $profileComplete = collect([
            $this->phone,
            $this->blood_group,
            $this->gender,
            $this->region,
            $this->date_of_birth,
        ])->every(fn ($value): bool => filled($value));

        $totalVolume = $this->relationLoaded('donations')
            ? $this->donations->where('status', 'completed')->sum('volume_ml')
            : $this->donations()->where('status', 'completed')->sum('volume_ml');
        $profilePhotoUrl = $this->profile_photo_path
            ? (str_starts_with($this->profile_photo_path, 'http://') || str_starts_with($this->profile_photo_path, 'https://')
                ? $this->profile_photo_path
                : asset('storage/' . $this->profile_photo_path))
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'profile_photo_path' => $this->profile_photo_path,
            'profile_photo_url' => $profilePhotoUrl,
            'role' => $this->role,
            'role_label' => str($this->role ?: 'donor')->title()->toString(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()),
            'is_active' => (bool) $this->is_active,
            'profile_complete' => $profileComplete,
            'is_profile_complete' => $profileComplete,
            'donor_profile_complete' => $profileComplete,
            'profileComplete' => $profileComplete,
            'blood_group' => $this->blood_group,
            'gender' => $this->gender ? ucfirst($this->gender) : null,
            'gender_value' => $this->gender,
            'region' => $this->region,
            'date_of_birth' => $this->date_of_birth,
            'date_of_birth_label' => optional($this->date_of_birth)->format('M d, Y'),
            'last_donation' => $this->last_donation,
            'last_donation_label' => optional($this->last_donation)->format('M d, Y'),
            'address' => $this->address,
            'donor_id' => $this->donorProfile?->donor_id,
            'preferred_center' => $this->donorProfile?->preferredCenter?->name,
            'preferred_center_id' => $this->donorProfile?->preferred_center_id,
            'preferred_center_address' => $this->donorProfile?->preferredCenter?->address,
            'loyalty_tier' => $this->donorProfile?->loyalty_tier,
            'loyalty_tier_label' => str($this->donorProfile?->loyalty_tier ?: 'bronze')->title()->toString(),
            'loyalty_points' => $this->donorProfile?->loyalty_points,
            'total_donations' => $this->donorProfile?->total_donations ?? 0,
            'total_volume_ml' => (int) $totalVolume,
            'total_volume_liters' => round($totalVolume / 1000, 2),
            'next_eligible_date' => $this->donorProfile?->next_eligible_donation_date,
            'next_eligible_at' => $this->donorProfile?->next_eligible_donation_date,
            'next_eligible_date_label' => optional($this->donorProfile?->next_eligible_donation_date)->format('M d, Y'),
            'emergency_contact_name' => $this->donorProfile?->emergency_contact_name,
            'emergency_contact_phone' => $this->donorProfile?->emergency_contact_phone,
            'push_notifications_enabled' => (bool) $this->donorProfile?->push_notifications_enabled,
            'sms_reminders_enabled' => (bool) $this->donorProfile?->sms_reminders_enabled,
            'share_anonymized_data' => (bool) $this->donorProfile?->share_anonymized_data,
            'language' => $this->donorProfile?->language,
            'language_label' => $this->donorProfile?->language === 'sw' ? 'Swahili' : 'English',
            'donor_profile' => new DonorProfileResource($this->whenLoaded('donorProfile')),
        ];
    }
}
