<?php

namespace Database\Factories;

use App\Models\OfflineCollectionSubmission;
use App\Models\OfflineIdentifierBatch;
use App\OfflineCollectionSubmissionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<OfflineCollectionSubmission> */
class OfflineCollectionSubmissionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $payload = [
            'donor_id' => 1,
            'actual_volume_ml' => 450,
            'outcome' => 'completed',
        ];

        return [
            'client_submission_id' => (string) Str::uuid(),
            'offline_identifier_batch_id' => OfflineIdentifierBatch::factory(),
            'offline_collection_device_id' => function (array $attributes): int {
                $batchId = (int) $attributes['offline_identifier_batch_id'];

                return OfflineIdentifierBatch::query()->findOrFail($batchId)->offline_collection_device_id;
            },
            'blood_center_id' => function (array $attributes): int {
                $batchId = (int) $attributes['offline_identifier_batch_id'];

                return OfflineIdentifierBatch::query()->findOrFail($batchId)->blood_center_id;
            },
            'submitted_by' => null,
            'donation_identifier' => 'TZNBTSTST'.now()->format('Y').fake()->unique()->numerify('######0'),
            'payload_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
            'payload' => $payload,
            'status' => OfflineCollectionSubmissionStatus::Received,
            'conflict_codes' => null,
            'collection_episode_id' => null,
            'reviewed_by' => null,
            'received_at' => now(),
            'reconciled_at' => null,
            'reviewed_at' => null,
            'review_reason' => null,
        ];
    }
}
