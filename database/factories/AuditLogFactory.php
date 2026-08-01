<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory()->staff(),
            'blood_center_id' => BloodCenter::factory(),
            'action' => 'appointments.status_changed',
            'subject_type' => Appointment::class,
            'subject_id' => Appointment::factory(),
            'metadata' => ['from_status' => 'pending', 'to_status' => 'confirmed'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'occurred_at' => now(),
            'previous_hash' => null,
            'record_hash' => hash('sha256', fake()->unique()->uuid()),
        ];
    }
}
