<?php

namespace Database\Factories;

use App\BloodGroup;
use App\LowStockAlertStatus;
use App\Models\BloodCenter;
use App\Models\LowStockAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LowStockAlert>
 */
class LowStockAlertFactory extends Factory
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
            'blood_group' => BloodGroup::OPositive,
            'available_units' => 2,
            'minimum_threshold' => 5,
            'status' => LowStockAlertStatus::Open,
            'resolved_at' => null,
        ];
    }
}
