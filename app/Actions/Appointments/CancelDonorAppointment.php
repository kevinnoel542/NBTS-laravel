<?php

namespace App\Actions\Appointments;

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class CancelDonorAppointment
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Appointment $appointment, User $donor): Appointment
    {
        return DB::transaction(function () use ($appointment, $donor): Appointment {
            $lockedAppointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            Gate::forUser($donor)->authorize('cancel', $lockedAppointment);

            $previousStatus = $lockedAppointment->status;
            $lockedAppointment->forceFill([
                'status' => AppointmentStatus::Cancelled,
                'cancelled_at' => now(),
            ])->save();

            $this->auditLogger->record(
                actor: $donor,
                action: 'appointments.cancelled',
                subject: $lockedAppointment,
                bloodCenter: $lockedAppointment->bloodCenter,
                metadata: [
                    'from_status' => $previousStatus->value,
                ],
            );

            return $lockedAppointment->load('bloodCenter');
        }, attempts: 3);
    }
}
