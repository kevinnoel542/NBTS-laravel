<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LowStockAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'blood_group' => $this->blood_group,
            'available_units' => $this->available_units,
            'minimum_threshold' => $this->minimum_threshold,
            'status' => $this->status,
            'resolved_at' => $this->resolved_at,
        ];
    }
}
