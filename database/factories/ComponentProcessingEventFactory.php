<?php

namespace Database\Factories;

use App\Models\BloodUnit;
use App\Models\ComponentProcessingEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentProcessingEvent>
 */
class ComponentProcessingEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_unit_id' => BloodUnit::factory(),
            'donation_id' => fn (array $attributes): int => BloodUnit::query()->findOrFail($attributes['blood_unit_id'])->donation_id,
            'operator_id' => User::factory()->staff(),
            'event_type' => 'component_production',
            'method' => 'centrifugation',
            'device_identifier' => fake()->bothify('SEP-###'),
            'started_at' => now()->subMinutes(45),
            'ended_at' => now(),
            'yield_summary' => ['components' => 1],
            'modifications' => [],
            'qc_samples' => ['visual_inspection' => 'passed'],
            'deviations' => [],
            'final_label_verified' => true,
            'notes' => null,
        ];
    }
}
