<?php

namespace Database\Factories;

use App\Models\NotificationOutbox;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationOutbox>
 */
class NotificationOutboxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->staff(),
            'recipient_id' => User::factory()->donor(),
            'user_notification_id' => null,
            'outbox_reference' => fake()->unique()->bothify('NOB-########'),
            'idempotency_key' => fake()->unique()->uuid(),
            'template_code' => 'appointment_reminder_v1',
            'alert_type' => 'appointment',
            'channel' => 'sms',
            'locale' => 'en',
            'recipient_hash' => hash('sha256', fake()->uuid()),
            'segment_criteria' => ['blood_group' => 'O+'],
            'payload_summary' => ['title' => 'Appointment reminder', 'body_length' => 80],
            'preferences_snapshot' => ['sms' => true, 'push' => true, 'email' => true],
            'consent_snapshot' => ['sms' => true, 'push' => true, 'email' => true],
            'quiet_hours' => ['enabled' => true, 'start' => '21:00', 'end' => '07:00'],
            'after_commit' => true,
            'non_coercive' => true,
            'provider' => null,
            'provider_message_id' => null,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 5,
            'next_attempt_at' => now(),
            'sent_at' => null,
            'failed_at' => null,
            'expires_at' => now()->addDays(7),
            'last_error' => null,
        ];
    }
}
