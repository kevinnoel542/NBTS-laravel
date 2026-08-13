<?php

namespace Database\Factories;

use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\ComponentInventoryAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentInventoryAdjustment>
 */
class ComponentInventoryAdjustmentFactory extends Factory
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
            'blood_center_id' => fn (array $attributes): int => BloodComponent::query()->findOrFail($attributes['blood_component_id'])->blood_center_id,
            'adjusted_by' => User::factory()->staff(),
            'independent_approved_by' => null,
            'previous_status' => ComponentStatus::Quarantined,
            'new_status' => ComponentStatus::Available,
            'reason' => 'component_release_authorized',
            'evidence_reference' => fake()->bothify('EVD-####'),
            'notes' => null,
            'adjusted_at' => now(),
        ];
    }
}
