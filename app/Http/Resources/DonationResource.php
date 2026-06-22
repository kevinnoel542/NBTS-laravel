<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'donor' => new UserResource($this->whenLoaded('user')),
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'blood_center_id' => $this->blood_center_id,
            'center_id' => $this->blood_center_id,
            'center_name' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->name),
            'blood_unit' => new BloodUnitResource($this->whenLoaded('bloodUnit')),
            'blood_unit_number' => $this->whenLoaded('bloodUnit', fn () => $this->bloodUnit?->unit_number),
            'appointment_id' => $this->appointment_id,
            'donation_type' => $this->donation_type,
            'type_label' => str($this->donation_type)->replace('_', ' ')->title()->toString(),
            'blood_group' => $this->blood_group,
            'blood_type' => $this->blood_group,
            'blood_group_verified' => (bool) $this->blood_group_verified,
            'volume_ml' => $this->volume_ml,
            'donation_date' => $this->donation_date,
            'donated_at' => $this->donation_date,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
