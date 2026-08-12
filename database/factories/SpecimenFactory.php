<?php

namespace Database\Factories;

use App\Models\CollectionEpisode;
use App\Models\Specimen;
use App\SpecimenStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Specimen> */
class SpecimenFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'collection_episode_id' => CollectionEpisode::factory(),
            'collection_container_id' => null,
            'specimen_identifier' => 'SPC-'.fake()->unique()->numerify('############'),
            'specimen_type' => 'serology',
            'status' => SpecimenStatus::Expected,
            'is_required' => true,
            'volume_ml' => 6,
            'collected_by' => null,
            'collected_at' => null,
            'handed_off_by' => null,
            'handed_off_at' => null,
            'handoff_recipient' => null,
            'rejection_reason' => null,
        ];
    }
}
