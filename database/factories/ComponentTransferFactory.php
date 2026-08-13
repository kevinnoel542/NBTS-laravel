<?php

namespace Database\Factories;

use App\LogisticsMovementStatus;
use App\Models\BloodCenter;
use App\Models\ComponentTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentTransfer>
 */
class ComponentTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_center_id' => BloodCenter::factory(),
            'destination_center_id' => BloodCenter::factory(),
            'requested_by' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'status' => LogisticsMovementStatus::Requested,
            'urgency' => 'routine',
            'reason' => 'Routine balancing of component stock',
            'package_seal' => fake()->bothify('SEAL-####'),
            'courier_name' => 'NBTS courier',
            'vehicle_identifier' => fake()->bothify('NBTS-###'),
            'departed_at' => null,
            'received_at' => null,
            'temperature_evidence' => ['pack_out_c' => 4.0],
            'discrepancy_notes' => null,
            'acceptance_decision' => null,
        ];
    }
}
