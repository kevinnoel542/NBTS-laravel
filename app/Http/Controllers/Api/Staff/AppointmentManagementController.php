<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AppointmentManagementController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

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

        $appointment->load(['user', 'bloodCenter']);
        $this->notificationService->notifyUser(
            $appointment->user,
            'Appointment confirmed',
            'Your appointment at ' . $appointment->bloodCenter?->name . ' is confirmed for ' . $appointment->scheduled_at->format('M d, Y H:i') . '.',
            'appointment_confirmed',
            ['appointment_id' => $appointment->id],
            '/appointments/' . $appointment->id,
        );

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

        $appointment->load(['user', 'bloodCenter']);
        $this->notificationService->notifyUser(
            $appointment->user,
            'Appointment cancelled',
            'Your appointment at ' . $appointment->bloodCenter?->name . ' was cancelled by staff.',
            'appointment_cancelled',
            ['appointment_id' => $appointment->id],
            '/appointments',
        );

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }
}
