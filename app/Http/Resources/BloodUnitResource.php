<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'donation_id' => $this->donation_id,
            'donor_id' => $this->donor_id,
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'blood_group' => $this->blood_group,
            'collection_date' => $this->collection_date,
            'expiry_date' => $this->expiry_date,
            'status' => $this->status,
            'current_location' => $this->current_location,
        ];
    }
}
