<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodCenterResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'phone_number' => $this->phone,
            'email' => $this->email,
            'opening_hours' => $this->opening_hours,
            'hours' => $this->opening_hours,
            'working_hours' => $this->opening_hours,
            'open_hours' => $this->opening_hours,
            'services' => $this->services ?? [],
            'service_list' => $this->services ?? [],
            'capacity_label' => $this->capacity_label,
            'capacity' => $this->capacity_label,
            'availability' => $this->capacity_label,
            'estimated_wait_minutes' => $this->estimated_wait_minutes,
            'wait_time' => $this->wait_time_label,
            'estimated_wait' => $this->wait_time_label,
            'wait_time_label' => $this->wait_time_label,
            'center_type' => $this->center_type,
            'center_type_label' => $this->center_type ? str($this->center_type)->title()->toString() : null,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'distance_km' => $this->distance_km ?? $this->distance ?? null,
            'is_active' => (bool) $this->is_active,
            'is_open' => (bool) $this->is_active,
            'open' => (bool) $this->is_active,
            'status_open' => (bool) $this->is_active,
            'status_label' => $this->status_label,
            'appointments_count' => $this->whenCounted('appointments'),
            'donations_count' => $this->whenCounted('donations'),
            'campaigns_count' => $this->whenCounted('campaigns'),
            'inventory_count' => $this->whenCounted('inventory'),
            'low_stock_alerts_count' => $this->whenCounted('lowStockAlerts'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
