<?php

namespace Database\Factories;

use App\LogisticsMovementStatus;
use App\Models\BloodComponent;
use App\Models\ComponentDispatch;
use App\Models\ComponentDispatchItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentDispatchItem>
 */
class ComponentDispatchItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'component_dispatch_id' => ComponentDispatch::factory(),
            'blood_component_id' => BloodComponent::factory(),
            'status' => LogisticsMovementStatus::Packed,
            'reconciled_disposition' => null,
            'reconciled_at' => null,
        ];
    }
}
