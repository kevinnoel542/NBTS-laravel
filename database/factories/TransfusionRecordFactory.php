<?php

namespace Database\Factories;

use App\Models\HospitalComponentAllocation;
use App\Models\HospitalComponentReceipt;
use App\Models\PatientSpecimen;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\TransfusionRecordStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransfusionRecord>
 */
class TransfusionRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_component_allocation_id' => HospitalComponentAllocation::factory(),
            'hospital_blood_request_id' => fn (array $attributes): int => HospitalComponentAllocation::query()->findOrFail($attributes['hospital_component_allocation_id'])->hospital_blood_request_id,
            'hospital_component_receipt_id' => fn (array $attributes): int => HospitalComponentReceipt::factory()->create(['hospital_component_allocation_id' => $attributes['hospital_component_allocation_id']])->id,
            'patient_specimen_id' => fn (array $attributes): int => PatientSpecimen::factory()->create(['hospital_blood_request_id' => $attributes['hospital_blood_request_id']])->id,
            'blood_component_id' => fn (array $attributes): int => HospitalComponentAllocation::query()->findOrFail($attributes['hospital_component_allocation_id'])->blood_component_id,
            'verified_by' => User::factory()->staff(),
            'recorded_by' => User::factory()->staff(),
            'status' => TransfusionRecordStatus::Completed,
            'bedside_checks' => ['right_patient' => true, 'right_component' => true, 'right_request' => true],
            'verified_at' => now(),
            'started_at' => now(),
            'completed_at' => now()->addHour(),
            'observations' => ['15_min' => 'stable', 'completion' => 'stable'],
            'volume_ml' => 280,
            'outcome' => 'completed_without_reaction',
            'unused_component_disposition' => null,
            'overdue_at' => null,
            'final_disposition_at' => now()->addHour(),
            'notes' => null,
        ];
    }
}
