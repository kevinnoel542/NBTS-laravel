<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'donation_threshold' => (int) $this->donation_threshold,
            'threshold_label' => $this->donation_threshold . ' donations',
            'is_active' => (bool) $this->is_active,
            'status_label' => $this->status_label,
        ];
    }
}
