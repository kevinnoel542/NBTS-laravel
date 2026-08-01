<?php

namespace App\Services;

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class AppointmentSlotService
{
    /**
     * @return list<array{
     *     time: string,
     *     slot_time: string,
     *     start_time: string,
     *     scheduled_at: string,
     *     starts_at: string,
     *     available: bool,
     *     is_available: bool,
     *     open: bool,
     *     reason_code: string|null,
     *     reason: string|null,
     *     message: string|null,
     *     status_label: string
     * }>
     */
    public function forDate(BloodCenter $bloodCenter, CarbonImmutable $date): array
    {
        $capacity = $this->capacity();
        $bookedCounts = Appointment::query()
            ->where('blood_center_id', $bloodCenter->id)
            ->whereBetween('scheduled_at', [$date->startOfDay(), $date->endOfDay()])
            ->whereIn('status', $this->activeStatuses())
            ->get(['scheduled_at'])
            ->countBy(fn (Appointment $appointment): string => $appointment->scheduled_at->format('H:i'));

        return array_map(function (string $time) use ($bloodCenter, $date, $capacity, $bookedCounts): array {
            $scheduledAt = $date->setTimeFromTimeString($time);
            $booked = (int) $bookedCounts->get($time, 0);
            $reasonCode = match (true) {
                ! $bloodCenter->is_active => 'center_closed',
                ! $scheduledAt->isFuture() => 'past',
                $booked >= $capacity => 'full',
                default => null,
            };
            $available = $reasonCode === null;
            $message = $reasonCode === null
                ? null
                : $this->translation('api.appointment_slot_'.$reasonCode);

            return [
                'time' => $time,
                'slot_time' => $time,
                'start_time' => $time,
                'scheduled_at' => $scheduledAt->toIso8601String(),
                'starts_at' => $scheduledAt->toIso8601String(),
                'available' => $available,
                'is_available' => $available,
                'open' => $available,
                'reason_code' => $reasonCode,
                'reason' => $message,
                'message' => $message,
                'status_label' => $available
                    ? $this->translation('api.appointment_slot_available')
                    : $message,
            ];
        }, $this->times());
    }

    /** @throws ValidationException */
    public function assertCanBook(
        User $donor,
        BloodCenter $bloodCenter,
        CarbonImmutable $scheduledAt,
        ?int $ignoredAppointmentId = null,
    ): void {
        if (! $bloodCenter->is_active) {
            throw ValidationException::withMessages([
                'blood_center_id' => [trans('api.appointment_center_inactive')],
            ]);
        }

        if (! $scheduledAt->isFuture()) {
            throw ValidationException::withMessages([
                'scheduled_at' => [trans('api.appointment_must_be_future')],
            ]);
        }

        if ($scheduledAt->isAfter(now()->addDays((int) config('nbts.appointment_booking_window_days', 90)))) {
            throw ValidationException::withMessages([
                'scheduled_at' => [trans('api.appointment_outside_booking_window')],
            ]);
        }

        if ($scheduledAt->second !== 0 || ! in_array($scheduledAt->format('H:i'), $this->times(), true)) {
            throw ValidationException::withMessages([
                'scheduled_at' => [trans('api.appointment_invalid_slot')],
            ]);
        }

        $hasActiveAppointment = Appointment::query()
            ->where('user_id', $donor->id)
            ->whereIn('status', $this->activeStatuses())
            ->when(
                $ignoredAppointmentId !== null,
                fn ($query) => $query->whereKeyNot($ignoredAppointmentId),
            )
            ->exists();

        if ($hasActiveAppointment) {
            throw ValidationException::withMessages([
                'scheduled_at' => [trans('api.appointment_active_exists')],
            ]);
        }

        $bookedCount = Appointment::query()
            ->where('blood_center_id', $bloodCenter->id)
            ->where('scheduled_at', $scheduledAt)
            ->whereIn('status', $this->activeStatuses())
            ->when(
                $ignoredAppointmentId !== null,
                fn ($query) => $query->whereKeyNot($ignoredAppointmentId),
            )
            ->count();

        if ($bookedCount >= $this->capacity()) {
            throw ValidationException::withMessages([
                'scheduled_at' => [trans('api.appointment_slot_full')],
            ]);
        }
    }

    /** @return list<string> */
    private function times(): array
    {
        $times = config('nbts.appointment_slot_times', []);

        return is_array($times)
            ? array_values(array_filter($times, 'is_string'))
            : [];
    }

    /** @return list<string> */
    private function activeStatuses(): array
    {
        return [AppointmentStatus::Pending->value, AppointmentStatus::Confirmed->value];
    }

    private function capacity(): int
    {
        return max(1, (int) config('nbts.appointment_slot_capacity', 1));
    }

    private function translation(string $key): string
    {
        $translation = trans($key);

        return is_string($translation) ? $translation : $key;
    }
}
