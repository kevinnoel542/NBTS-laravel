<?php

namespace Database\Factories;

use App\HospitalStatus;
use App\Models\Hospital;
use App\Models\HospitalService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalService>
 */
class HospitalServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory(),
            'code' => fake()->unique()->bothify('WARD-###'),
            'name' => 'Medical ward',
            'service_type' => 'ward',
            'status' => HospitalStatus::Active,
            'contacts' => ['nurse_station' => fake()->phoneNumber()],
            'capabilities' => ['transfusion' => true],
            'operating_hours' => ['mode' => '24/7'],
            'request_routes' => ['routine' => 'blood_bank_queue'],
        ];
    }
}
