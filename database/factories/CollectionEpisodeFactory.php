<?php

namespace Database\Factories;

use App\CollectionEpisodeStatus;
use App\EligibilityStatus;
use App\Models\BloodCenter;
use App\Models\CollectionEpisode;
use App\Models\DonorIdentityCheck;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollectionEpisode> */
class CollectionEpisodeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'donation_identifier' => 'TZNBTS'.fake()->unique()->numerify('############'),
            'donor_id' => User::factory()->donor()->afterCreating(function (User $user): void {
                DonorProfile::factory()->create(['user_id' => $user, 'eligibility_status' => EligibilityStatus::Eligible]);
            }),
            'blood_center_id' => BloodCenter::factory(),
            'appointment_id' => null,
            'identity_check_id' => DonorIdentityCheck::factory(),
            'eligibility_record_id' => EligibilityRecord::factory()->eligible(),
            'donation_id' => null,
            'status' => CollectionEpisodeStatus::Prepared,
            'outcome' => null,
            'donation_method' => 'whole_blood',
            'bag_type' => 'CPDA-1 whole-blood bag',
            'bag_lot' => fake()->bothify('LOT-####-??'),
            'device_identifier' => null,
            'planned_volume_ml' => 450,
            'actual_volume_ml' => null,
            'started_at' => null,
            'ended_at' => null,
            'prepared_by' => null,
            'collected_by' => null,
            'source_mode' => 'online',
            'aftercare_confirmed_at' => null,
            'donor_acknowledged_at' => null,
            'notes' => null,
        ];
    }
}
