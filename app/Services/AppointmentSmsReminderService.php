<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\SmsReminderLog;
use Illuminate\Support\Carbon;

class AppointmentSmsReminderService
{
    public const REMINDER_DAYS = [7, 3, 1];

    public function __construct(private SmsService $smsService)
    {
    }

    public function sendDueReminders(?Carbon $today = null): int
    {
        $today ??= now();
        $sent = 0;

        foreach (self::REMINDER_DAYS as $daysBefore) {
            $targetDate = $today->copy()->addDays($daysBefore)->toDateString();
            $reminderKey = "{$daysBefore}_days_before";

            Appointment::query()
                ->with(['user.donorProfile', 'bloodCenter'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereDate('scheduled_at', $targetDate)
                ->chunkById(100, function ($appointments) use ($reminderKey, &$sent): void {
                    foreach ($appointments as $appointment) {
                        if ($this->sendReminder($appointment, $reminderKey)) {
                            $sent++;
                        }
                    }
                });
        }

        return $sent;
    }

    private function sendReminder(Appointment $appointment, string $reminderKey): bool
    {
        $user = $appointment->user;
        $profile = $user?->donorProfile;

        if (! $user || ! $profile || ! $profile->sms_reminders_enabled) {
            return false;
        }

        $phone = $user->phone;

        if (! filled($phone)) {
            return false;
        }

        if (SmsReminderLog::where('appointment_id', $appointment->id)->where('reminder_key', $reminderKey)->exists()) {
            return false;
        }

        $message = $this->message($appointment);
        $result = $this->smsService->send($phone, $message);

        SmsReminderLog::create([
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'reminder_key' => $reminderKey,
            'phone' => $phone,
            'message' => $message,
            'provider' => $result['provider'] ?? config('services.sms.driver', 'log'),
            'status' => ($result['sent'] ?? false) ? 'sent' : 'failed',
            'provider_message_id' => $result['provider_message_id'] ?? null,
            'error' => $result['error'] ?? null,
            'sent_at' => ($result['sent'] ?? false) ? now() : null,
        ]);

        return (bool) ($result['sent'] ?? false);
    }

    private function message(Appointment $appointment): string
    {
        $center = $appointment->bloodCenter?->name ?? 'your NBTS center';
        $scheduled = $appointment->scheduled_at->format('D, M d Y H:i');

        return "NBTS reminder: your blood donation appointment at {$center} is scheduled for {$scheduled}. If you cannot attend, please reschedule or cancel in the NBTS app.";
    }
}
