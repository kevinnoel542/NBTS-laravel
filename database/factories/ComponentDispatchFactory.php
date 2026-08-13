<?php

namespace Database\Factories;

use App\LogisticsMovementStatus;
use App\Models\BloodCenter;
use App\Models\ComponentDispatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentDispatch>
 */
class ComponentDispatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_center_id' => BloodCenter::factory(),
            'dispatched_by' => User::factory()->staff(),
            'received_by' => null,
            'request_reference' => fake()->bothify('REQ-####'),
            'destination_name' => 'Approved hospital blood bank',
            'route' => 'Main route',
            'eta_at' => now()->addHours(2),
            'courier_name' => 'NBTS courier',
            'vehicle_identifier' => fake()->bothify('NBTS-###'),
            'package_identifier' => fake()->bothify('PKG-####'),
            'logger_device_id' => null,
            'status' => LogisticsMovementStatus::Packed,
            'chain_of_custody' => ['dispatch desk', 'courier'],
            'proof_of_receipt' => null,
            'dispatched_at' => now(),
            'delivered_at' => null,
        ];
    }
}
