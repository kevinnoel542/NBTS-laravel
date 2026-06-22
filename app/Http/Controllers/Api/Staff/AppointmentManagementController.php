<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentManagementController extends Controller
{
    public function confirm(Appointment $appointment, Request $request)
    {
        if (!$request->user()->can('appointments.manage')) {
            abort(403);
        }

        if ($appointment->status !== 'pending') {
            return response()->json(['message' => 'Only pending appointments can be confirmed'], 422);
        }

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'handled_by' => $request->user()->id,
        ]);

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    public function cancel(Appointment $appointment, Request $request)
    {
        if (!$request->user()->can('appointments.manage')) {
            abort(403);
        }

        if (in_array($appointment->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'This appointment cannot be cancelled'], 422);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'handled_by' => $request->user()->id,
        ]);

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }
}
