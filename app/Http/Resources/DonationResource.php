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
            'blood_unit' => new BloodUnitResource($this->whenLoaded('bloodUnit')),
            'appointment_id' => $this->appointment_id,
            'donation_type' => $this->donation_type,
            'blood_group' => $this->blood_group,
            'blood_group_verified' => (bool) $this->blood_group_verified,
            'volume_ml' => $this->volume_ml,
            'donation_date' => $this->donation_date,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
