<?php

namespace Database\Factories;

use App\ComponentReservationStatus;
use App\Models\BloodComponent;
use App\Models\ComponentReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentReservation>
 */
class ComponentReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_component_id' => BloodComponent::factory(),
            'requested_by' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'status' => ComponentReservationStatus::Active,
            'reason' => 'Approved clinical reservation',
            'exception_reason' => null,
            'reserved_at' => now(),
            'reserved_until' => now()->addHours(6),
            'released_at' => null,
        ];
    }
}
