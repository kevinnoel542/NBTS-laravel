<?php

namespace Database\Factories;

use App\ComponentReturnDisposition;
use App\Models\BloodComponent;
use App\Models\ComponentReturnAssessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentReturnAssessment>
 */
class ComponentReturnAssessmentFactory extends Factory
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
            'assessed_by' => User::factory()->staff(),
            'received_at' => now(),
            'temperature_min_c' => 2.00,
            'temperature_max_c' => 6.00,
            'package_condition' => 'intact',
            'chain_of_custody' => ['dispatch', 'courier', 'return desk'],
            'disposition' => ComponentReturnDisposition::Restock,
            'accepted_for_restock' => true,
            'evidence_reference' => fake()->bothify('RET-####'),
            'notes' => null,
            'assessed_at' => now(),
        ];
    }
}
