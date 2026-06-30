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
            'center_id' => $this->blood_center_id,
            'center_name' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->name),
            'center_address' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->address),
            'blood_group' => $this->blood_group,
            'available_units' => (int) $this->available_units,
            'reserved_units' => (int) $this->reserved_units,
            'minimum_threshold' => (int) $this->minimum_threshold,
            'total_units' => $this->total_units,
            'stock_gap' => $this->stock_gap,
            'stock_status' => $this->stock_status,
            'stock_status_label' => str($this->stock_status)->title()->toString(),
            'is_low_stock' => $this->available_units < $this->minimum_threshold,
            'is_critical' => $this->stock_status === 'critical',
            'updated_at' => $this->updated_at,
        ];
    }
}
