<?php

namespace App\Console\Commands;

use App\Services\AppointmentReminderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notifications:send-appointment-reminders')]
#[Description('Queue due donor appointment reminders without sending duplicates')]
class SendAppointmentRemindersCommand extends Command
{
    public function handle(AppointmentReminderService $appointmentReminderService): int
    {
        $dispatched = $appointmentReminderService->sendDueReminders();
        $this->components->info("Queued {$dispatched} appointment reminder(s).");

        return self::SUCCESS;
    }
}
