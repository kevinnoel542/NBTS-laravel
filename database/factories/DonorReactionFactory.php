<?php

namespace Database\Factories;

use App\DonorReactionSeverity;
use App\Models\CollectionEpisode;
use App\Models\DonorReaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DonorReaction> */
class DonorReactionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'collection_episode_id' => CollectionEpisode::factory(),
            'donor_id' => function (array $attributes): int {
                $episodeId = (int) $attributes['collection_episode_id'];

                return CollectionEpisode::query()->findOrFail($episodeId)->donor_id;
            },
            'blood_center_id' => function (array $attributes): int {
                $episodeId = (int) $attributes['collection_episode_id'];

                return CollectionEpisode::query()->findOrFail($episodeId)->blood_center_id;
            },
            'severity' => DonorReactionSeverity::Mild,
            'reaction_type' => 'vasovagal_symptoms',
            'symptoms' => ['dizziness'],
            'occurred_at' => now(),
            'treatment' => 'Donor rested under observation.',
            'referral' => null,
            'outcome' => 'Resolved before departure.',
            'followup_required' => false,
            'followup_due_at' => null,
            'recorded_by' => null,
        ];
    }
}
