<?php

namespace Database\Factories;

use App\Models\ProtectedExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProtectedExport>
 */
class ProtectedExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requested_by' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'export_reference' => fake()->unique()->bothify('EXP-########'),
            'purpose' => 'Regulator-approved safety review',
            'recipient' => 'National public health authority',
            'scope' => ['fields' => ['component_identifier', 'reaction_type'], 'date_range' => 'current-quarter'],
            'delivery_channel' => 'encrypted_download',
            'encrypted_manifest' => ['checksum' => fake()->sha256(), 'format' => 'csv'],
            'status' => 'approved',
            'expires_at' => now()->addDays(7),
            'approved_at' => now(),
            'downloaded_at' => null,
        ];
    }
}
