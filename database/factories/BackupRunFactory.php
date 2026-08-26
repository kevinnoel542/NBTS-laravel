<?php

namespace Database\Factories;

use App\Models\BackupRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackupRun>
 */
class BackupRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operator_id' => User::factory()->staff(),
            'backup_reference' => fake()->unique()->bothify('BKP-########'),
            'backup_type' => 'database',
            'storage_location' => 'offsite-vault-primary',
            'encrypted' => true,
            'offsite' => true,
            'size_bytes' => 1024 * 1024,
            'checksum' => fake()->sha256(),
            'status' => 'verified',
            'started_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(10),
            'verified_at' => now()->subMinutes(5),
            'restore_tested_at' => null,
            'retention_until' => now()->addYear(),
            'failure_reason' => null,
        ];
    }
}
