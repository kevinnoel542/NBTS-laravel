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
            'blood_center_id' => $this->blood_center_id,
            'center_id' => $this->blood_center_id,
            'center_name' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->name),
            'center_address' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->address),
            'blood_group' => $this->blood_group,
            'available_units' => (int) $this->available_units,
            'minimum_threshold' => (int) $this->minimum_threshold,
            'stock_gap' => $this->stock_gap,
            'severity' => $this->severity,
            'severity_label' => str($this->severity)->title()->toString(),
            'status' => $this->status,
            'status_label' => str($this->status)->replace('_', ' ')->title()->toString(),
            'is_active' => $this->is_active,
            'campaign_id' => $this->whenLoaded('campaign', fn () => $this->campaign?->id),
            'campaign_title' => $this->whenLoaded('campaign', fn () => $this->campaign?->title),
            'resolved_at' => $this->resolved_at,
            'resolved_at_label' => optional($this->resolved_at)->format('M d, Y h:i A'),
            'created_at' => $this->created_at,
            'created_at_label' => optional($this->created_at)->format('M d, Y h:i A'),
            'updated_at' => $this->updated_at,
        ];
    }
}
