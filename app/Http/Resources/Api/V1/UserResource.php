<?php

namespace App\Http\Resources\Api\V1;

use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        assert($user instanceof User);

        $donorProfile = $user->relationLoaded('donorProfile')
            ? $user->donorProfile
            : null;
        $preferredCenter = $donorProfile instanceof DonorProfile
            ? $donorProfile->preferredCenter
            : null;
        $profilePhotoUrl = $this->profilePhotoUrl($user);
        $profileComplete = $user->phone !== null
            && $user->blood_group !== null
            && $user->gender !== null
            && $user->region !== null
            && $user->date_of_birth !== null;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'blood_group' => $user->blood_group?->value,
            'gender' => $user->gender?->value,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'region' => $user->region,
            'address' => $user->address,
            'profile_photo_url' => $profilePhotoUrl,
            'photo_url' => $profilePhotoUrl,
            'locale' => $user->locale,
            'language' => $user->locale,
            'profile_complete' => $profileComplete,
            'donor_id' => $donorProfile?->donor_id,
            'preferred_center_id' => $preferredCenter?->id,
            'preferred_center' => $preferredCenter?->name,
            'emergency_contact_name' => $donorProfile?->emergency_contact_name,
            'emergency_contact_phone' => $donorProfile?->emergency_contact_phone,
            'push_notifications_enabled' => $donorProfile?->push_notifications_enabled,
            'email_notifications_enabled' => $donorProfile?->email_notifications_enabled,
            'sms_reminders_enabled' => $donorProfile?->sms_reminders_enabled,
            'loyalty_tier' => $donorProfile?->loyalty_tier,
            'loyalty_points' => $donorProfile?->loyalty_points,
            'total_donations' => $donorProfile?->total_donations,
            'total_volume_ml' => (int) ($user->getAttribute('completed_donations_sum_volume_ml') ?? 0),
            'next_eligible_date' => $donorProfile?->next_eligible_donation_date?->toDateString(),
            'share_anonymized_data' => $donorProfile?->share_anonymized_data,
            'roles' => $this->when(
                $user->relationLoaded('roles'),
                fn (): array => $user->roles->pluck('name')->values()->all(),
            ),
            'donor_profile' => $this->when(
                $donorProfile instanceof DonorProfile,
                fn (): array => [
                    'donor_id' => $donorProfile->donor_id,
                    'blood_group_status' => $donorProfile->blood_group_status->value,
                    'blood_group_status_label' => __('operations.status.'.$donorProfile->blood_group_status->value),
                    'blood_group_verified' => $donorProfile->blood_group_verified,
                    'eligibility_status' => $donorProfile->eligibility_status->value,
                    'eligibility_status_label' => __('operations.status.'.$donorProfile->eligibility_status->value),
                    'next_eligible_donation_date' => $donorProfile->next_eligible_donation_date?->toDateString(),
                    'total_donations' => $donorProfile->total_donations,
                    'loyalty_tier' => $donorProfile->loyalty_tier,
                    'loyalty_points' => $donorProfile->loyalty_points,
                    'emergency_contact_name' => $donorProfile->emergency_contact_name,
                    'emergency_contact_phone' => $donorProfile->emergency_contact_phone,
                    'push_notifications_enabled' => $donorProfile->push_notifications_enabled,
                    'email_notifications_enabled' => $donorProfile->email_notifications_enabled,
                    'sms_reminders_enabled' => $donorProfile->sms_reminders_enabled,
                    'share_anonymized_data' => $donorProfile->share_anonymized_data,
                    'language' => $donorProfile->language,
                    'preferred_center' => $preferredCenter === null ? null : [
                        'id' => $preferredCenter->id,
                        'name' => $preferredCenter->name,
                    ],
                ],
            ),
        ];
    }

    private function profilePhotoUrl(User $user): ?string
    {
        $path = $user->profile_photo_path;

        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
