<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAdjustmentResource extends JsonResource
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
            'blood_unit_id' => $this->blood_unit_id,
            'unit_number' => $this->whenLoaded('bloodUnit', fn () => $this->bloodUnit?->unit_number),
            'blood_group' => $this->blood_group,
            'quantity_delta' => (int) $this->quantity_delta,
            'direction' => $this->direction,
            'direction_label' => str($this->direction)->replace('_', ' ')->title()->toString(),
            'reason' => $this->reason,
            'reason_label' => str($this->reason)->replace('_', ' ')->title()->toString(),
            'notes' => $this->notes,
            'adjusted_by' => $this->adjusted_by,
            'adjusted_by_name' => $this->whenLoaded('adjuster', fn () => $this->adjuster?->name),
            'created_at' => $this->created_at,
            'created_at_label' => optional($this->created_at)->format('M d, Y h:i A'),
            'updated_at' => $this->updated_at,
        ];
    }
}
