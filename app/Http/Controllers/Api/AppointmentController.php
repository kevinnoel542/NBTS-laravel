<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        $appointments = $this->appointmentService->getUserAppointments($request->user()->id);
        return AppointmentResource::collection($appointments->load(['user', 'bloodCenter']));
    }

    public function upcoming(Request $request)
    {
        $appointments = $this->appointmentService->getUserUpcomingAppointments($request->user()->id);
        return AppointmentResource::collection($appointments->load(['user', 'bloodCenter']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'blood_center_id' => 'required|exists:blood_centers,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $appointment = $this->appointmentService->bookAppointment(array_merge($request->all(), [
            'user_id' => $request->user()->id
        ]));

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    public function show($id, Request $request)
    {
        $appointment = $this->appointmentService->getAppointmentDetails($id);
        
        if (!$appointment || $appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    public function cancel($id, Request $request)
    {
        $appointment = $this->appointmentService->getAppointmentDetails($id);

        if (!$appointment || $appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        if (in_array($appointment->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'This appointment cannot be cancelled'], 422);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }
}
