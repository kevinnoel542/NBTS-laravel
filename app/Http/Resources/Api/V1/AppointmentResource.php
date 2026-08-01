<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $appointment = $this->resource;
        assert($appointment instanceof Appointment);

        return [
            'id' => $appointment->id,
            'appointment_id' => $appointment->id,
            'blood_center_id' => $appointment->blood_center_id,
            'center_id' => $appointment->blood_center_id,
            'center_name' => $appointment->bloodCenter->name,
            'blood_center' => new BloodCenterResource($appointment->bloodCenter),
            'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
            'starts_at' => $appointment->scheduled_at->toIso8601String(),
            'status' => $appointment->status->value,
            'status_label' => __('operations.status.'.$appointment->status->value),
            'confirmed_at' => $appointment->confirmed_at?->toIso8601String(),
            'cancelled_at' => $appointment->cancelled_at?->toIso8601String(),
            'rescheduled_at' => $appointment->rescheduled_at?->toIso8601String(),
            'notes' => $appointment->notes,
        ];
    }
}
