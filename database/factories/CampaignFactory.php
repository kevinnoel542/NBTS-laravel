<?php

namespace Database\Factories;

use App\CampaignStatus;
use App\CampaignType;
use App\Models\BloodCenter;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'start_date' => now()->addDays(7)->setTime(9, 0),
            'end_date' => now()->addDays(7)->setTime(16, 0),
            'blood_center_id' => BloodCenter::factory(),
            'location' => fake()->city(),
            'image_path' => null,
            'status' => CampaignStatus::Upcoming,
            'campaign_type' => CampaignType::Standard,
            'target_blood_group' => null,
            'low_stock_alert_id' => null,
        ];
    }

    public function ongoing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => CampaignStatus::Ongoing,
        ]);
    }

    public function emergency(): static
    {
        return $this->state(fn (array $attributes): array => [
            'campaign_type' => CampaignType::Emergency,
        ]);
    }
}
