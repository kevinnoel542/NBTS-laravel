<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Reward;
use Illuminate\Database\Seeder;

class LoyaltySeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'First Donation', 'slug' => 'first-donation', 'description' => 'Awarded after the first completed donation.', 'icon' => 'heart', 'donation_threshold' => 1],
            ['name' => 'Life Saver', 'slug' => 'life-saver', 'description' => 'Awarded after three completed donations.', 'icon' => 'shield-check', 'donation_threshold' => 3],
            ['name' => 'Community Hero', 'slug' => 'community-hero', 'description' => 'Awarded after five completed donations.', 'icon' => 'star', 'donation_threshold' => 5],
            ['name' => 'NBTS Champion', 'slug' => 'nbts-champion', 'description' => 'Awarded after ten completed donations.', 'icon' => 'trophy', 'donation_threshold' => 10],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge + ['is_active' => true]);
        }

        $rewards = [
            ['name' => 'Recognition Certificate', 'slug' => 'recognition-certificate', 'description' => 'Certificate for committed donors.', 'donation_threshold' => 3],
            ['name' => 'Priority Campaign Invite', 'slug' => 'priority-campaign-invite', 'description' => 'Priority invitations to recognition events.', 'donation_threshold' => 5],
        ];

        foreach ($rewards as $reward) {
            Reward::updateOrCreate(['slug' => $reward['slug']], $reward + ['is_active' => true]);
        }
    }
}
