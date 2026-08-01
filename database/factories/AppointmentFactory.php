<?php

namespace Database\Factories;

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->donor(),
            'blood_center_id' => BloodCenter::factory(),
            'scheduled_at' => now()->addDays(fake()->numberBetween(1, 30))->setTime(9, 0),
            'status' => AppointmentStatus::Pending,
            'confirmed_at' => null,
            'cancelled_at' => null,
            'rescheduled_at' => null,
            'handled_by' => null,
            'notes' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'confirmed_at' => now(),
            'status' => AppointmentStatus::Confirmed,
        ]);
    }
}
