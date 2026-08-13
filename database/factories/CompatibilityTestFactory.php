<?php

namespace Database\Factories;

use App\BloodGroup;
use App\CompatibilityResult;
use App\CompatibilityTestStatus;
use App\Models\BloodComponent;
use App\Models\CompatibilityTest;
use App\Models\HospitalBloodRequest;
use App\Models\PatientSpecimen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompatibilityTest>
 */
class CompatibilityTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_blood_request_id' => HospitalBloodRequest::factory(),
            'patient_specimen_id' => fn (array $attributes): int => PatientSpecimen::factory()->create(['hospital_blood_request_id' => $attributes['hospital_blood_request_id']])->id,
            'blood_component_id' => BloodComponent::factory(),
            'performed_by' => User::factory()->staff(),
            'reviewed_by' => User::factory()->staff(),
            'emergency_release_authorization_id' => null,
            'method' => 'gel_card_crossmatch',
            'instrument_identifier' => fake()->bothify('XMT-###'),
            'reagent_lot' => fake()->bothify('RGT-####'),
            'control_result' => 'valid',
            'abo_rh_confirmation' => BloodGroup::OPositive,
            'antibody_screen_result' => 'negative',
            'compatibility_result' => CompatibilityResult::Compatible,
            'status' => CompatibilityTestStatus::Reviewed,
            'performed_at' => now()->subMinutes(30),
            'reviewed_at' => now()->subMinutes(20),
            'valid_until' => now()->addDays(3),
            'exception_reason' => null,
            'notes' => null,
        ];
    }
}
