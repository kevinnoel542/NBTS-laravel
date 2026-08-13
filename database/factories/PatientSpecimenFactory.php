<?php

namespace Database\Factories;

use App\BloodGroup;
use App\Models\HospitalBloodRequest;
use App\Models\PatientSpecimen;
use App\Models\User;
use App\PatientSpecimenStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientSpecimen>
 */
class PatientSpecimenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_blood_request_id' => HospitalBloodRequest::factory(),
            'hospital_id' => fn (array $attributes): int => HospitalBloodRequest::query()->findOrFail($attributes['hospital_blood_request_id'])->hospital_id,
            'collected_by' => User::factory()->staff(),
            'received_by' => User::factory()->staff(),
            'specimen_identifier' => fake()->unique()->bothify('PSP-########'),
            'patient_reference' => fn (array $attributes): string => HospitalBloodRequest::query()->findOrFail($attributes['hospital_blood_request_id'])->patient_reference,
            'patient_reference_hash' => fn (array $attributes): string => HospitalBloodRequest::query()->findOrFail($attributes['hospital_blood_request_id'])->patient_reference_hash,
            'positive_identification_method' => 'wristband and request form',
            'blood_group' => BloodGroup::OPositive,
            'antibody_screen_result' => 'negative',
            'status' => PatientSpecimenStatus::Received,
            'collected_at' => now()->subHour(),
            'received_at' => now()->subMinutes(45),
            'expires_at' => now()->addDays(3),
            'rejection_reason' => null,
        ];
    }
}
