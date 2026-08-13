<?php

namespace Database\Factories;

use App\HospitalAllocationStatus;
use App\Models\BloodComponent;
use App\Models\CompatibilityTest;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalComponentAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalComponentAllocation>
 */
class HospitalComponentAllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_blood_request_id' => HospitalBloodRequest::factory(),
            'blood_component_id' => BloodComponent::factory(),
            'compatibility_test_id' => null,
            'emergency_release_authorization_id' => null,
            'allocated_by' => User::factory()->staff(),
            'issue_checked_by' => null,
            'status' => HospitalAllocationStatus::Allocated,
            'allocated_at' => now(),
            'expires_at' => now()->addHours(6),
            'issued_at' => null,
            'final_check' => null,
            'issue_reference' => fake()->unique()->bothify('ISS-########'),
            'notes' => null,
        ];
    }

    public function compatible(): static
    {
        return $this->state(fn (array $attributes): array => [
            'compatibility_test_id' => CompatibilityTest::factory()->create([
                'blood_component_id' => $attributes['blood_component_id'],
                'hospital_blood_request_id' => $attributes['hospital_blood_request_id'],
            ])->id,
        ]);
    }
}
