<?php

namespace Database\Factories;

use App\BloodGroup;
use App\Models\BloodCenter;
use App\Models\InventoryAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryAdjustment>
 */
class InventoryAdjustmentFactory extends Factory
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
            'blood_unit_id' => null,
            'adjusted_by' => User::factory()->staff(),
            'blood_group' => BloodGroup::OPositive,
            'quantity_delta' => 1,
            'reason' => 'Blood unit added to available inventory',
            'notes' => null,
        ];
    }
}
