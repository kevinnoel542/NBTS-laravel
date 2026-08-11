<?php

namespace App\Actions\Appointments;

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\User;
use App\Services\AppointmentSlotService;
use App\Support\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class RescheduleStaffAppointment
{
    public function __construct(
        private AppointmentSlotService $appointmentSlotService,
        private AuditLogger $auditLogger,
    ) {}

    public function execute(
        Appointment $appointment,
        User $actor,
        int $bloodCenterId,
        string $scheduledAt,
        string $reason,
    ): Appointment {
        $reason = trim($reason);

        if (mb_strlen($reason) < 10) {
            throw ValidationException::withMessages([
                'appointmentRescheduleReason' => [__('console.workflow.reason_required')],
            ]);
        }

        $scheduled = CarbonImmutable::parse($scheduledAt, (string) config('app.timezone'))
            ->setTimezone((string) config('app.timezone'));

        return DB::transaction(function () use ($appointment, $actor, $bloodCenterId, $scheduled, $reason): Appointment {
            $bloodCenter = BloodCenter::query()->lockForUpdate()->findOrFail($bloodCenterId);
            $lockedAppointment = Appointment::query()
                ->with('donor')
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            Gate::forUser($actor)->authorize('rescheduleStaff', [$lockedAppointment, $bloodCenter]);
            $this->appointmentSlotService->assertCanBook(
                $lockedAppointment->donor,
                $bloodCenter,
                $scheduled,
                $lockedAppointment->id,
            );

            $previousCenterId = $lockedAppointment->blood_center_id;
            $previousScheduledAt = $lockedAppointment->scheduled_at->toIso8601String();
            $previousStatus = $lockedAppointment->status;

            $lockedAppointment->forceFill([
                'blood_center_id' => $bloodCenter->id,
                'scheduled_at' => $scheduled,
                'status' => AppointmentStatus::Confirmed,
                'confirmed_at' => now(),
                'rescheduled_at' => now(),
                'handled_by' => $actor->id,
                'notes' => $reason,
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'appointments.staff_rescheduled',
                subject: $lockedAppointment,
                bloodCenter: $bloodCenter,
                metadata: [
                    'from_blood_center_id' => $previousCenterId,
                    'from_scheduled_at' => $previousScheduledAt,
                    'from_status' => $previousStatus->value,
                    'reason' => $reason,
                    'to_scheduled_at' => $scheduled->toIso8601String(),
                ],
            );

            return $lockedAppointment->refresh()->load(['bloodCenter', 'donor']);
        }, attempts: 3);
    }
}
