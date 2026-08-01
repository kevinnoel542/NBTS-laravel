<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Appointments\BookDonorAppointment;
use App\Actions\Appointments\CancelDonorAppointment;
use App\Actions\Appointments\RescheduleDonorAppointment;
use App\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AppointmentSlotsRequest;
use App\Http\Requests\Api\V1\BookAppointmentRequest;
use App\Http\Requests\Api\V1\RescheduleAppointmentRequest;
use App\Http\Resources\Api\V1\AppointmentResource;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\User;
use App\Services\AppointmentSlotService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class AppointmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $this->user($request);
        Gate::forUser($user)->authorize('viewAny', Appointment::class);
        $perPage = min(max($request->integer('per_page', 20), 1), 50);

        $appointments = Appointment::query()
            ->where('user_id', $user->id)
            ->with('bloodCenter')
            ->latest('scheduled_at')
            ->paginate($perPage)
            ->withQueryString();

        return AppointmentResource::collection($appointments);
    }

    public function upcoming(Request $request): AppointmentResource|JsonResponse
    {
        $user = $this->user($request);
        Gate::forUser($user)->authorize('viewAny', Appointment::class);

        $appointment = Appointment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->where('scheduled_at', '>', now())
            ->with('bloodCenter')
            ->oldest('scheduled_at')
            ->first();

        return $appointment === null
            ? response()->json(['data' => null])
            : new AppointmentResource($appointment);
    }

    public function store(
        BookAppointmentRequest $request,
        BookDonorAppointment $bookDonorAppointment,
    ): AppointmentResource {
        $data = $request->bookingData();
        $appointment = $bookDonorAppointment->handle(
            donor: $this->user($request),
            bloodCenterId: $data['blood_center_id'],
            scheduledAt: $data['scheduled_at'],
            notes: $data['notes'],
        );

        return new AppointmentResource($appointment);
    }

    public function show(Request $request, Appointment $appointment): AppointmentResource
    {
        Gate::forUser($this->user($request))->authorize('view', $appointment);

        return new AppointmentResource($appointment->load('bloodCenter'));
    }

    public function update(
        RescheduleAppointmentRequest $request,
        Appointment $appointment,
        RescheduleDonorAppointment $rescheduleDonorAppointment,
    ): AppointmentResource {
        $data = $request->appointmentData();
        $updatedAppointment = $rescheduleDonorAppointment->handle(
            appointment: $appointment,
            donor: $this->user($request),
            bloodCenterId: $data['blood_center_id'] ?? $appointment->blood_center_id,
            scheduledAt: $data['scheduled_at'],
            notes: $data['notes'],
        );

        return new AppointmentResource($updatedAppointment);
    }

    public function cancel(
        Request $request,
        Appointment $appointment,
        CancelDonorAppointment $cancelDonorAppointment,
    ): AppointmentResource {
        return new AppointmentResource(
            $cancelDonorAppointment->handle($appointment, $this->user($request)),
        );
    }

    public function availableSlots(
        AppointmentSlotsRequest $request,
        BloodCenter $bloodCenter,
        AppointmentSlotService $appointmentSlotService,
    ): JsonResponse {
        abort_unless($bloodCenter->is_active, 404);
        $date = CarbonImmutable::parse(
            $request->appointmentDate(),
            (string) config('app.timezone'),
        )->startOfDay();

        return response()->json([
            'data' => $appointmentSlotService->forDate($bloodCenter, $date),
        ]);
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }
}
