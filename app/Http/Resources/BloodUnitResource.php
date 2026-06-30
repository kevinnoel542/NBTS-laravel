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
            'donor_name' => $this->whenLoaded('donor', fn () => $this->donor?->name),
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'blood_center_id' => $this->blood_center_id,
            'center_id' => $this->blood_center_id,
            'center_name' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->name),
            'blood_group' => $this->blood_group,
            'collection_date' => $this->collection_date,
            'expiry_date' => $this->expiry_date,
            'collection_date_label' => optional($this->collection_date)->format('M d, Y'),
            'expiry_date_label' => optional($this->expiry_date)->format('M d, Y'),
            'days_to_expiry' => $this->days_to_expiry,
            'expiry_status' => $this->expiry_status,
            'expiry_status_label' => str($this->expiry_status)->replace('_', ' ')->title()->toString(),
            'status' => $this->status,
            'status_label' => str($this->status)->replace('_', ' ')->title()->toString(),
            'current_location' => $this->current_location,
            'handled_by' => $this->handled_by,
            'handler_name' => $this->whenLoaded('handler', fn () => $this->handler?->name),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
