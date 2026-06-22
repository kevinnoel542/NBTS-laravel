<?php

namespace Database\Seeders;

use App\Models\BloodCenter;
use Illuminate\Database\Seeder;

class BloodCenterSeeder extends Seeder
{
    public function run(): void
    {
        BloodCenter::query()
            ->whereIn('email', [
                'bank@cityhospital.co.ke',
                'info@nbts.go.ke',
                'blood@redcross.or.ke',
            ])
            ->orWhereIn('name', [
                'City Hospital Blood Bank',
                'National Blood Transfusion Service',
                'Red Cross Blood Drive Center',
            ])
            ->delete();

        $centers = [
            [
                'name' => 'Muhimbili National Hospital Blood Bank',
                'address' => 'United Nations Road, Upanga',
                'city' => 'Dar es Salaam',
                'phone' => '+255222151367',
                'email' => 'bloodbank@mnh.or.tz',
                'opening_hours' => 'Mon - Fri 08:00 - 17:00, Sat 09:00 - 13:00',
                'services' => ['Whole blood', 'Donor screening', 'Emergency donations'],
                'capacity_label' => 'High availability',
                'estimated_wait_minutes' => 20,
                'center_type' => 'Hospital blood bank',
                'latitude' => -6.806553,
                'longitude' => 39.280338,
                'is_active' => true,
            ],
            [
                'name' => 'National Blood Transfusion Service - Eastern Zone',
                'address' => 'Ilala, Dar es Salaam',
                'city' => 'Dar es Salaam',
                'phone' => '+255222861010',
                'email' => 'eastern@nbts.go.tz',
                'opening_hours' => 'Mon - Fri 08:00 - 17:00',
                'services' => ['Whole blood', 'Campaign registration', 'Donor card support'],
                'capacity_label' => 'Moderate availability',
                'estimated_wait_minutes' => 15,
                'center_type' => 'Regional NBTS center',
                'latitude' => -6.827755,
                'longitude' => 39.269213,
                'is_active' => true,
            ],
            [
                'name' => 'Benjamin Mkapa Hospital Blood Bank',
                'address' => 'University of Dodoma Campus',
                'city' => 'Dodoma',
                'phone' => '+255262963710',
                'email' => 'bloodbank@bmh.or.tz',
                'opening_hours' => 'Mon - Fri 08:00 - 16:00, Sat 09:00 - 12:00',
                'services' => ['Whole blood', 'Platelet referral', 'Donor screening'],
                'capacity_label' => 'Appointments preferred',
                'estimated_wait_minutes' => 25,
                'center_type' => 'Hospital blood bank',
                'latitude' => -6.218185,
                'longitude' => 35.746426,
                'is_active' => true,
            ],
            [
                'name' => 'Bugando Medical Centre Blood Bank',
                'address' => 'Wurzburg Road',
                'city' => 'Mwanza',
                'phone' => '+255282500051',
                'email' => 'bloodbank@bugandomedicalcentre.go.tz',
                'opening_hours' => 'Mon - Fri 08:00 - 17:00',
                'services' => ['Whole blood', 'Emergency donations', 'Donor screening'],
                'capacity_label' => 'Open for donors',
                'estimated_wait_minutes' => 30,
                'center_type' => 'Hospital blood bank',
                'latitude' => -2.516431,
                'longitude' => 32.904745,
                'is_active' => true,
            ],
        ];

        foreach ($centers as $center) {
            BloodCenter::updateOrCreate(
                ['email' => $center['email']],
                $center,
            );
        }
    }
}
