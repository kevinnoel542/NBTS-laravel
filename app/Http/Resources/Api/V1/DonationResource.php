<?php

namespace App\Http\Resources\Api\V1;

use App\DonationStatus;
use App\Models\BloodCenter;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $donation = $this->resource;
        assert($donation instanceof Donation);
        $bloodCenter = $donation->relationLoaded('bloodCenter')
            ? $donation->bloodCenter
            : null;

        return [
            'id' => $donation->id,
            'donation_id' => $donation->id,
            'blood_center_id' => $donation->blood_center_id,
            'center_id' => $donation->blood_center_id,
            'center_name' => $bloodCenter?->name,
            'blood_center' => $bloodCenter instanceof BloodCenter
                ? new BloodCenterResource($bloodCenter)
                : null,
            'appointment_id' => $donation->appointment_id,
            'donation_type' => $donation->donation_type->value,
            'type_label' => str($donation->donation_type->value)->replace('_', ' ')->title()->toString(),
            'blood_group' => $donation->blood_group->value,
            'blood_type' => $donation->blood_group->value,
            'blood_group_verified' => $donation->blood_group_verified,
            'volume_ml' => $donation->volume_ml,
            'volume_liters' => round($donation->volume_ml / 1000, 2),
            'donation_date' => $donation->donation_date->toDateString(),
            'donated_at' => $donation->donation_date->toDateString(),
            'date' => $donation->donation_date->toDateString(),
            'status' => $donation->status->value,
            'is_completed' => $donation->status === DonationStatus::Completed,
            'is_failed' => $donation->status === DonationStatus::Failed,
        ];
    }
}
