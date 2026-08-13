<?php

namespace Database\Factories;

use App\LogisticsMovementStatus;
use App\Models\BloodComponent;
use App\Models\ComponentTransfer;
use App\Models\ComponentTransferItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentTransferItem>
 */
class ComponentTransferItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'component_transfer_id' => ComponentTransfer::factory(),
            'blood_component_id' => BloodComponent::factory(),
            'status' => LogisticsMovementStatus::Requested,
            'source_confirmed_at' => null,
            'destination_confirmed_at' => null,
            'accepted' => null,
        ];
    }
}
