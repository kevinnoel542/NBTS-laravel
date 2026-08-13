<?php

namespace Database\Factories;

use App\Models\BloodComponent;
use App\Models\ComponentDisposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentDisposal>
 */
class ComponentDisposalFactory extends Factory
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
            'disposed_by' => User::factory()->staff(),
            'witnessed_by' => User::factory()->staff(),
            'approved_by' => null,
            'method' => 'biohazard_incineration',
            'reason' => 'expired',
            'quantity' => 1,
            'location' => 'Controlled disposal room',
            'evidence_reference' => fake()->bothify('DSP-####'),
            'disposed_at' => now(),
        ];
    }
}
