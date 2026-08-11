<?php

namespace App\Actions\Appointments;

use App\AppointmentStatus;
use App\Exceptions\InvalidAppointmentTransition;
use App\Models\Appointment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TransitionAppointment
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(
        Appointment $appointment,
        AppointmentStatus $status,
        User $actor,
        ?string $notes = null,
    ): Appointment {
        return DB::transaction(function () use ($appointment, $status, $actor, $notes): Appointment {
            $lockedAppointment = Appointment::query()
                ->lockForUpdate()
                ->whereKey($appointment->getKey())
                ->firstOrFail();

            Gate::forUser($actor)->authorize('transition', $lockedAppointment);

            $previousStatus = $lockedAppointment->status;

            if (! $previousStatus->canTransitionTo($status)) {
                throw InvalidAppointmentTransition::from($previousStatus, $status);
            }

            $timestamp = now();

            match ($status) {
                AppointmentStatus::Confirmed => $lockedAppointment->confirmed_at = $timestamp,
                AppointmentStatus::CheckedIn => $lockedAppointment->checked_in_at = $timestamp,
                AppointmentStatus::Cancelled => $lockedAppointment->cancelled_at = $timestamp,
                AppointmentStatus::NoShow => $lockedAppointment->no_show_at = $timestamp,
                AppointmentStatus::Completed, AppointmentStatus::Pending => null,
            };

            $lockedAppointment->status = $status;
            $lockedAppointment->handled_by = $actor->id;

            if ($notes !== null) {
                $lockedAppointment->notes = trim($notes);
            }

            $lockedAppointment->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'appointments.status_changed',
                subject: $lockedAppointment,
                bloodCenter: $lockedAppointment->bloodCenter,
                metadata: [
                    'from_status' => $previousStatus->value,
                    'notes_provided' => $notes !== null,
                    'to_status' => $status->value,
                ],
            );

            return $lockedAppointment->refresh();
        }, attempts: 3);
    }
}
