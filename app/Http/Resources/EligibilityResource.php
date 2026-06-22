<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EligibilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this['status'],
            'eligible' => $this['eligible'],
            'message' => $this['message'],
            'reasons' => $this['reasons'],
            'next_eligible_donation_date' => $this['next_eligible_donation_date'],
        ];
    }
}
