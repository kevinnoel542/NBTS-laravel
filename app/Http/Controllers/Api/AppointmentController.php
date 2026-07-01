<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use App\Services\NotificationService;
use App\Models\Appointment;
use App\Models\BloodCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    protected $appointmentService;
    protected $notificationService;

    public function __construct(AppointmentService $appointmentService, NotificationService $notificationService)
    {
        $this->appointmentService = $appointmentService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $appointments = $this->appointmentService->getUserAppointments($request->user()->id);
        return AppointmentResource::collection($appointments->load(['user', 'bloodCenter']));
    }

    public function upcoming(Request $request)
    {
        $appointments = $this->appointmentService->getUserUpcomingAppointments($request->user()->id);
        $appointment = $appointments->first();

        if (!$appointment) {
            return response()->json(['data' => null]);
        }

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'blood_center_id' => 'required|exists:blood_centers,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $this->validateAppointmentSlot($request->user()->id, (int) $request->blood_center_id, $request->scheduled_at);

        $appointment = $this->appointmentService->bookAppointment(array_merge($request->all(), [
            'user_id' => $request->user()->id
        ]));

        $appointment->load('bloodCenter');
        $this->notificationService->notifyUser(
            $request->user(),
            'Appointment booked',
            'Your appointment at ' . $appointment->bloodCenter?->name . ' is booked for ' . $appointment->scheduled_at->format('M d, Y H:i') . '.',
            'appointment_booked',
            ['appointment_id' => $appointment->id],
            '/appointments/' . $appointment->id,
        );

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    public function update($id, Request $request)
    {
        $appointment = $this->appointmentService->getAppointmentDetails($id);

        if (!$appointment || $appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        if (in_array($appointment->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'This appointment cannot be rescheduled'], 422);
        }

        $data = $request->validate([
            'blood_center_id' => 'sometimes|exists:blood_centers,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $bloodCenterId = (int) ($data['blood_center_id'] ?? $appointment->blood_center_id);
        $this->validateAppointmentSlot($request->user()->id, $bloodCenterId, $data['scheduled_at'], $appointment->id);

        $appointment->update([
            'blood_center_id' => $bloodCenterId,
            'scheduled_at' => $data['scheduled_at'],
            'notes' => $data['notes'] ?? $appointment->notes,
            'status' => 'pending',
            'rescheduled_at' => now(),
        ]);

        $appointment->load('bloodCenter');
        $this->notificationService->notifyUser(
            $request->user(),
            'Appointment rescheduled',
            'Your appointment at ' . $appointment->bloodCenter?->name . ' was rescheduled to ' . $appointment->scheduled_at->format('M d, Y H:i') . '.',
            'appointment_rescheduled',
            ['appointment_id' => $appointment->id],
            '/appointments/' . $appointment->id,
        );

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    public function slots(Request $request)
    {
        $data = $request->validate([
            'center_id' => 'required|exists:blood_centers,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $bloodCenter = BloodCenter::findOrFail($data['center_id']);
        $date = Carbon::parse($data['date'], config('app.timezone'));

        return response()->json([
            'data' => $this->buildAppointmentSlots($bloodCenter, $date),
        ]);
    }

    public function availableSlots(BloodCenter $bloodCenter, Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($data['date'], config('app.timezone'));

        return response()->json([
            'data' => $this->buildAppointmentSlots($bloodCenter, $date),
        ]);
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

        $appointment->load('bloodCenter');
        $this->notificationService->notifyUser(
            $request->user(),
            'Appointment cancelled',
            'Your appointment at ' . $appointment->bloodCenter?->name . ' was cancelled.',
            'appointment_cancelled',
            ['appointment_id' => $appointment->id],
            '/appointments',
        );

        return new AppointmentResource($appointment->load(['user', 'bloodCenter']));
    }

    private function validateAppointmentSlot(int $userId, int $bloodCenterId, string $scheduledAt, ?int $ignoreAppointmentId = null): void
    {
        $center = BloodCenter::findOrFail($bloodCenterId);

        if (!$center->is_active) {
            throw ValidationException::withMessages([
                'blood_center_id' => ['This blood center is not accepting appointments.'],
            ]);
        }

        $scheduled = \Illuminate\Support\Carbon::parse($scheduledAt);

        $duplicateUserAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignoreAppointmentId, fn ($query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->exists();

        if ($duplicateUserAppointment) {
            throw ValidationException::withMessages([
                'scheduled_at' => ['You already have an active appointment. Cancel or reschedule it first.'],
            ]);
        }

        $slotTaken = Appointment::where('blood_center_id', $bloodCenterId)
            ->where('scheduled_at', $scheduled)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignoreAppointmentId, fn ($query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->exists();

        if ($slotTaken) {
            throw ValidationException::withMessages([
                'scheduled_at' => ['This appointment slot is already booked.'],
            ]);
        }
    }

    private function buildAppointmentSlots(BloodCenter $bloodCenter, Carbon $date)
    {
        return collect(['08:00', '09:30', '11:00', '13:00', '14:30', '16:00'])
            ->map(function (string $time) use ($date, $bloodCenter) {
                $scheduledAt = $date->copy()->setTimeFromTimeString($time);
                $booked = Appointment::where('blood_center_id', $bloodCenter->id)
                    ->where('scheduled_at', $scheduledAt)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

                $available = $scheduledAt->isFuture() && ! $booked && $bloodCenter->is_active;
                $reason = null;

                if (! $bloodCenter->is_active) {
                    $reason = 'Center closed';
                } elseif (! $scheduledAt->isFuture()) {
                    $reason = 'Past time';
                } elseif ($booked) {
                    $reason = 'Full';
                }

                return [
                    'time' => $time,
                    'slot_time' => $time,
                    'start_time' => $time,
                    'scheduled_time' => $scheduledAt->toDateTimeString(),
                    'scheduled_at' => $scheduledAt->toISOString(),
                    'starts_at' => $scheduledAt->toISOString(),
                    'available' => $available,
                    'is_available' => $available,
                    'open' => $available,
                    'reason' => $reason,
                    'message' => $reason,
                    'status_label' => $available ? 'Available' : $reason,
                ];
            })
            ->values();
    }
}
