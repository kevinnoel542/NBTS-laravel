<?php

namespace Database\Factories;

use App\CollectionContainerStatus;
use App\Models\CollectionContainer;
use App\Models\CollectionEpisode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollectionContainer> */
class CollectionContainerFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'collection_episode_id' => CollectionEpisode::factory(),
            'container_identifier' => 'CNT-'.fake()->unique()->numerify('############'),
            'kind' => 'primary',
            'manufacturer_lot' => fake()->bothify('LOT-####-??'),
            'status' => CollectionContainerStatus::Quarantined,
            'quarantine_location' => 'Collection quarantine',
            'created_by' => null,
            'quarantined_at' => now(),
        ];
    }
}
