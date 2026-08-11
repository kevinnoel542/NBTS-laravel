<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\SmsReminderLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsReminderLog>
 */
class SmsReminderLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'user_id' => function (array $attributes): int {
                return Appointment::query()
                    ->whereKey($attributes['appointment_id'])
                    ->firstOrFail()
                    ->user_id;
            },
            'reminder_key' => '1_day_before',
            'phone' => '+255700000000',
            'message' => 'NBTS appointment reminder.',
            'provider' => 'log',
            'status' => 'pending',
            'provider_message_id' => null,
            'error' => null,
            'sent_at' => null,
        ];
    }
}
