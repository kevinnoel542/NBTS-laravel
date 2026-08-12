<?php

namespace Database\Factories;

use App\Models\DonorDuplicateCase;
use App\Models\DonorIdentityAlias;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DonorIdentityAlias> */
class DonorIdentityAliasFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'canonical_donor_id' => User::factory()->donor(),
            'source_donor_id' => User::factory()->donor(),
            'duplicate_case_id' => DonorDuplicateCase::factory(),
            'source_donor_identifier' => 'DNR-'.fake()->unique()->numerify('########'),
            'merged_by' => null,
            'reason' => 'Confirmed duplicate donor identity after controlled review.',
            'merged_at' => now(),
        ];
    }
}
