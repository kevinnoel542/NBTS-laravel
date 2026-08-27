<?php

namespace Database\Factories;

use App\Models\DocumentSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSnapshot>
 */
class DocumentSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'generated_by' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'document_reference' => fake()->unique()->bothify('DOC-########'),
            'document_type' => 'donor_summary',
            'locale' => 'en',
            'source_period_start' => today()->startOfMonth(),
            'source_period_end' => today()->endOfMonth(),
            'stable_identifiers' => ['donor_id' => fake()->bothify('DNR-########')],
            'labels' => ['title' => 'Donor summary', 'issued_at' => 'Issued at'],
            'access_scope' => ['permission' => 'reports.export', 'center_scope' => true],
            'verification_context' => ['source' => 'authoritative_records', 'version' => 1],
            'encrypted_snapshot_payload' => ['summary' => 'privacy-safe snapshot'],
            'checksum' => fake()->sha256(),
            'authorized' => true,
            'audited' => true,
            'large_export' => false,
            'queued' => false,
            'queue_name' => null,
            'status' => 'generated',
            'generated_at' => now(),
            'approved_at' => now(),
            'expires_at' => now()->addDays(7),
        ];
    }
}
