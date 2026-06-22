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
            'services' => $this->services ?? [],
            'capacity_label' => $this->capacity_label,
            'estimated_wait_minutes' => $this->estimated_wait_minutes,
            'wait_time' => $this->estimated_wait_minutes ? $this->estimated_wait_minutes . ' min wait' : null,
            'center_type' => $this->center_type,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'is_active' => (bool) $this->is_active,
            'is_open' => (bool) $this->is_active,
        ];
    }
}
