<?php

namespace Database\Factories;

use App\HospitalStatus;
use App\Models\Hospital;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hospital>
 */
class HospitalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_unit_id' => OrganizationUnit::factory()->hospital(),
            'code' => fake()->unique()->bothify('HSP-####'),
            'name' => fake()->company().' Hospital',
            'status' => HospitalStatus::Active,
            'blood_bank_level' => 'regional',
            'contacts' => ['blood_bank' => fake()->phoneNumber(), 'email' => fake()->safeEmail()],
            'capabilities' => ['crossmatch' => true, 'emergency_release' => true],
            'operating_hours' => ['mode' => '24/7'],
            'request_routes' => ['routine' => 'electronic', 'emergency' => 'phone_plus_electronic'],
            'integration_identifier' => fake()->unique()->bothify('HIE-####'),
            'minimum_patient_identity_fields' => ['patient_reference', 'birth_year', 'gender'],
            'privacy_policy_version' => 'patient-min-v1',
            'approved_at' => now(),
            'approved_by' => User::factory()->staff(),
        ];
    }
}
