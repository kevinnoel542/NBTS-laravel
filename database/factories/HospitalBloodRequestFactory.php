<?php

namespace Database\Factories;

use App\BloodGroup;
use App\HospitalRequestStatus;
use App\HospitalRequestUrgency;
use App\Models\ComponentProductCatalog;
use App\Models\Hospital;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalBloodRequest>
 */
class HospitalBloodRequestFactory extends Factory
{
    public function definition(): array
    {
        $patientReference = fake()->unique()->bothify('PAT-########');

        return [
            'hospital_id' => Hospital::factory(),
            'hospital_service_id' => fn (array $attributes): int => HospitalService::factory()->create(['hospital_id' => $attributes['hospital_id']])->id,
            'requested_by' => User::factory()->staff(),
            'reviewed_by' => null,
            'component_product_catalog_id' => ComponentProductCatalog::factory(),
            'request_reference' => fake()->unique()->bothify('HBR-########'),
            'patient_reference' => $patientReference,
            'patient_reference_hash' => hash('sha256', $patientReference),
            'patient_birth_year' => 1988,
            'patient_gender' => 'female',
            'diagnosis' => 'Severe anaemia',
            'indication' => 'Symptomatic anaemia',
            'hemoglobin_g_dl' => 6.80,
            'observations' => ['blood_pressure' => '110/70'],
            'active_bleeding' => false,
            'urgency' => HospitalRequestUrgency::Routine,
            'requested_blood_group' => BloodGroup::OPositive,
            'quantity_requested' => 1,
            'quantity_allocated' => 0,
            'required_at' => now()->addHours(6),
            'attachments' => [],
            'notes' => null,
            'guidance_snapshot' => ['rule' => 'patient-blood-management-v1'],
            'override_reason' => null,
            'source_mode' => 'electronic',
            'status' => HospitalRequestStatus::Submitted,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'partially_filled_at' => null,
            'fulfilled_at' => null,
            'cancelled_at' => null,
        ];
    }
}
