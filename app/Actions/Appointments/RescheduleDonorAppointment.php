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

final readonly class RescheduleDonorAppointment
{
    public function __construct(
        private AppointmentSlotService $appointmentSlotService,
        private AuditLogger $auditLogger,
    ) {}

    public function handle(
        Appointment $appointment,
        User $donor,
        int $bloodCenterId,
        string $scheduledAt,
        ?string $notes = null,
    ): Appointment {
        $scheduled = CarbonImmutable::parse($scheduledAt, (string) config('app.timezone'))
            ->setTimezone((string) config('app.timezone'));

        return DB::transaction(function () use ($appointment, $donor, $bloodCenterId, $scheduled, $notes): Appointment {
            $bloodCenter = BloodCenter::query()->lockForUpdate()->findOrFail($bloodCenterId);
            $lockedAppointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            Gate::forUser($donor)->authorize('reschedule', $lockedAppointment);
            $this->appointmentSlotService->assertCanBook(
                $donor,
                $bloodCenter,
                $scheduled,
                $lockedAppointment->id,
            );

            $previousCenterId = $lockedAppointment->blood_center_id;
            $previousScheduledAt = $lockedAppointment->scheduled_at->toIso8601String();

            $lockedAppointment->forceFill([
                'blood_center_id' => $bloodCenter->id,
                'scheduled_at' => $scheduled,
                'status' => AppointmentStatus::Pending,
                'confirmed_at' => null,
                'rescheduled_at' => now(),
                'handled_by' => null,
                'notes' => $notes === null ? $lockedAppointment->notes : trim($notes),
            ])->save();

            $this->auditLogger->record(
                actor: $donor,
                action: 'appointments.rescheduled',
                subject: $lockedAppointment,
                bloodCenter: $bloodCenter,
                metadata: [
                    'from_blood_center_id' => $previousCenterId,
                    'from_scheduled_at' => $previousScheduledAt,
                    'to_scheduled_at' => $scheduled->toIso8601String(),
                ],
            );

            return $lockedAppointment->load('bloodCenter');
        }, attempts: 3);
    }
}
