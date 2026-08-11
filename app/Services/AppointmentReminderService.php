<?php

namespace App\Services;

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\SmsReminderLog;
use App\Models\UserNotification;
use App\Services\Notifications\DispatchUserNotification;
use Carbon\CarbonImmutable;

final readonly class AppointmentReminderService
{
    /** @var list<int> */
    public const REMINDER_DAYS = [7, 3, 1];

    public function __construct(private DispatchUserNotification $dispatchUserNotification) {}

    public function sendDueReminders(?CarbonImmutable $today = null): int
    {
        $today ??= today()->toImmutable();
        $dispatched = 0;

        foreach (self::REMINDER_DAYS as $daysBefore) {
            $targetDate = $today->addDays($daysBefore)->toDateString();
            $reminderKey = $daysBefore === 1 ? '1_day_before' : "{$daysBefore}_days_before";

            Appointment::query()
                ->with(['bloodCenter', 'donor.donorProfile', 'donor.fcmTokens'])
                ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                ->whereDate('scheduled_at', $targetDate)
                ->orderBy('id')
                ->chunkById(100, function ($appointments) use ($daysBefore, $reminderKey, &$dispatched): void {
                    foreach ($appointments as $appointment) {
                        $donor = $appointment->donor;
                        $profile = $donor->donorProfile;

                        if (! $donor->is_active || $profile === null) {
                            continue;
                        }

                        $locale = in_array($donor->locale, ['en', 'sw'], true) ? $donor->locale : 'en';
                        $title = trans('console.notifications.appointment_reminder_title', locale: $locale);
                        $body = trans('console.notifications.appointment_reminder_body', [
                            'center' => $appointment->bloodCenter->name,
                            'date' => $appointment->scheduled_at->translatedFormat('d M Y'),
                            'time' => $appointment->scheduled_at->format('H:i'),
                        ], $locale);
                        $sourceKey = "appointment:{$appointment->id}:{$reminderKey}";
                        $notification = UserNotification::query()->firstOrCreate(
                            ['source_key' => $sourceKey],
                            [
                                'action_url' => route('donate', absolute: false),
                                'body' => $body,
                                'data' => [
                                    'appointment_id' => $appointment->id,
                                    'blood_center_id' => $appointment->blood_center_id,
                                    'days_before' => $daysBefore,
                                    'reminder_key' => $reminderKey,
                                ],
                                'read_at' => null,
                                'sent_at' => now(),
                                'title' => $title,
                                'type' => 'appointment_reminder',
                                'user_id' => $donor->id,
                            ],
                        );

                        if (! $notification->wasRecentlyCreated) {
                            continue;
                        }

                        if ($profile->sms_reminders_enabled && filled($donor->phone)) {
                            SmsReminderLog::query()->firstOrCreate(
                                [
                                    'appointment_id' => $appointment->id,
                                    'reminder_key' => $reminderKey,
                                ],
                                [
                                    'message' => $body,
                                    'phone' => $donor->phone,
                                    'provider' => null,
                                    'status' => 'pending',
                                    'user_id' => $donor->id,
                                ],
                            );
                        }

                        $this->dispatchUserNotification->execute($notification, $donor);
                        $dispatched++;
                    }
                });
        }

        return $dispatched;
    }
}
