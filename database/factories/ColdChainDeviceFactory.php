<?php

namespace Database\Factories;

use App\ColdChainDeviceStatus;
use App\ColdChainDeviceType;
use App\Models\BloodCenter;
use App\Models\ColdChainDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ColdChainDevice>
 */
class ColdChainDeviceFactory extends Factory
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
            'device_code' => fake()->unique()->bothify('FRG-###'),
            'name' => 'Main blood refrigerator',
            'device_type' => ColdChainDeviceType::Refrigerator,
            'status' => ColdChainDeviceStatus::Active,
            'location' => 'Component storage room',
            'capacity_units' => 120,
            'responsible_staff_id' => User::factory()->staff(),
            'temperature_min_c' => 2.00,
            'temperature_max_c' => 6.00,
            'calibration_due_on' => today()->addMonths(3),
            'maintenance_due_on' => today()->addMonths(6),
            'alarm_config' => ['response_minutes' => 30],
        ];
    }
}
