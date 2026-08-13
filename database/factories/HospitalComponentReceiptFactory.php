<?php

namespace Database\Factories;

use App\HospitalReceiptStatus;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalComponentAllocation;
use App\Models\HospitalComponentReceipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalComponentReceipt>
 */
class HospitalComponentReceiptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_component_allocation_id' => HospitalComponentAllocation::factory(),
            'hospital_blood_request_id' => fn (array $attributes): int => HospitalComponentAllocation::query()->findOrFail($attributes['hospital_component_allocation_id'])->hospital_blood_request_id,
            'blood_component_id' => fn (array $attributes): int => HospitalComponentAllocation::query()->findOrFail($attributes['hospital_component_allocation_id'])->blood_component_id,
            'hospital_id' => fn (array $attributes): int => HospitalBloodRequest::query()->findOrFail($attributes['hospital_blood_request_id'])->hospital_id,
            'received_by' => User::factory()->staff(),
            'status' => HospitalReceiptStatus::Accepted,
            'received_at' => now(),
            'condition' => 'intact',
            'temperature_evidence' => ['receipt_c' => 4.2],
            'discrepancy_notes' => null,
            'chain_of_custody' => ['courier', 'hospital blood bank'],
        ];
    }
}
