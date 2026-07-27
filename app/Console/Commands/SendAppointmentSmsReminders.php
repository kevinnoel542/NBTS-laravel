<?php

namespace App\Console\Commands;

use App\Services\AppointmentSmsReminderService;
use Illuminate\Console\Command;

class SendAppointmentSmsReminders extends Command
{
    protected $signature = 'nbts:sms-appointment-reminders';

    protected $description = 'Send due SMS reminders for active donor appointments.';

    public function handle(AppointmentSmsReminderService $reminders): int
    {
        $sent = $reminders->sendDueReminders();

        $this->info("Sent {$sent} appointment SMS reminder(s).");

        return self::SUCCESS;
    }
}
