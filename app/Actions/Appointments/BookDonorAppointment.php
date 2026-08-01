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

final readonly class BookDonorAppointment
{
    public function __construct(
        private AppointmentSlotService $appointmentSlotService,
        private AuditLogger $auditLogger,
    ) {}

    public function handle(
        User $donor,
        int $bloodCenterId,
        string $scheduledAt,
        ?string $notes = null,
    ): Appointment {
        Gate::forUser($donor)->authorize('create', Appointment::class);
        $scheduled = CarbonImmutable::parse($scheduledAt, (string) config('app.timezone'))
            ->setTimezone((string) config('app.timezone'));

        return DB::transaction(function () use ($donor, $bloodCenterId, $scheduled, $notes): Appointment {
            $bloodCenter = BloodCenter::query()->lockForUpdate()->findOrFail($bloodCenterId);

            $this->appointmentSlotService->assertCanBook($donor, $bloodCenter, $scheduled);

            $appointment = Appointment::query()->create([
                'user_id' => $donor->id,
                'blood_center_id' => $bloodCenter->id,
                'scheduled_at' => $scheduled,
                'status' => AppointmentStatus::Pending,
                'notes' => $this->notes($notes),
            ]);

            $this->auditLogger->record(
                actor: $donor,
                action: 'appointments.booked',
                subject: $appointment,
                bloodCenter: $bloodCenter,
                metadata: [
                    'scheduled_at' => $scheduled->toIso8601String(),
                ],
            );

            return $appointment->load('bloodCenter');
        }, attempts: 3);
    }

    private function notes(?string $notes): ?string
    {
        $notes = $notes === null ? null : trim($notes);

        return $notes === '' ? null : $notes;
    }
}
