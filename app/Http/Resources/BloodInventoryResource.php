<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'blood_center_id' => $this->blood_center_id,
            'blood_group' => $this->blood_group,
            'available_units' => $this->available_units,
            'reserved_units' => $this->reserved_units,
            'minimum_threshold' => $this->minimum_threshold,
            'is_low_stock' => $this->available_units < $this->minimum_threshold,
        ];
    }
}
