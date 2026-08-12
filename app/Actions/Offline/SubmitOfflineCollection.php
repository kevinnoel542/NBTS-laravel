<?php

namespace App\Actions\Offline;

use App\Data\SubmitOfflineCollectionData;
use App\Models\CollectionEpisode;
use App\Models\OfflineCollectionDevice;
use App\Models\OfflineCollectionSubmission;
use App\Models\OfflineIdentifierBatch;
use App\Models\User;
use App\OfflineCollectionDeviceStatus;
use App\OfflineCollectionSubmissionStatus;
use App\Services\CollectionIdentifierService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;

final readonly class SubmitOfflineCollection
{
    public function __construct(
        private CollectionIdentifierService $identifierService,
        private AuditLogger $auditLogger,
    ) {}

    /** @throws JsonException */
    public function handle(User $actor, SubmitOfflineCollectionData $data): OfflineCollectionSubmission
    {
        $payloadJson = json_encode($data->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $payloadHash = hash('sha256', $payloadJson);
        $existing = OfflineCollectionSubmission::query()->where('client_submission_id', $data->clientSubmissionId)->first();

        if ($existing !== null) {
            if ($existing->offline_collection_device_id !== $data->deviceId
                || $existing->submitted_by !== $actor->id
                || ! hash_equals($existing->payload_hash, $payloadHash)
                || $existing->donation_identifier !== $data->donationIdentifier) {
                throw ValidationException::withMessages(['client_submission_id' => ['The submission identifier was already used for different content.']]);
            }

            return $existing;
        }

        return DB::transaction(function () use ($actor, $data, $payloadHash): OfflineCollectionSubmission {
            $device = OfflineCollectionDevice::query()->with('bloodCenter')->lockForUpdate()->findOrFail($data->deviceId);
            $batch = OfflineIdentifierBatch::query()->lockForUpdate()->findOrFail($data->identifierBatchId);

            if ($device->status !== OfflineCollectionDeviceStatus::Active
                || ($device->assigned_to !== null && $device->assigned_to !== $actor->id)
                || $batch->offline_collection_device_id !== $device->id
                || $batch->blood_center_id !== $device->blood_center_id
                || $batch->revoked_at !== null
                || $batch->expires_at->isPast()) {
                throw ValidationException::withMessages(['device' => ['The device or identifier batch is not active for this operator.']]);
            }

            $sequence = $this->sequenceInBatch($device, $batch, $data->donationIdentifier);
            if ($sequence === null) {
                throw ValidationException::withMessages(['donation_identifier' => ['The identifier is not part of the assigned offline batch.']]);
            }

            if (OfflineCollectionSubmission::query()->where('donation_identifier', $data->donationIdentifier)->exists()
                || CollectionEpisode::query()->where('donation_identifier', $data->donationIdentifier)->exists()) {
                throw ValidationException::withMessages(['donation_identifier' => ['The collection identifier has already been received or reconciled.']]);
            }

            $submission = OfflineCollectionSubmission::query()->create([
                'client_submission_id' => $data->clientSubmissionId,
                'offline_collection_device_id' => $device->id,
                'offline_identifier_batch_id' => $batch->id,
                'blood_center_id' => $device->blood_center_id,
                'submitted_by' => $actor->id,
                'donation_identifier' => $data->donationIdentifier,
                'payload_hash' => $payloadHash,
                'payload' => $data->payload,
                'status' => OfflineCollectionSubmissionStatus::Received,
                'received_at' => now(),
            ]);
            $batch->forceFill(['next_sequence' => max($batch->next_sequence, $sequence + 1)])->save();
            $device->forceFill(['last_synced_at' => now()])->save();
            $this->auditLogger->record($actor, 'offline.collection_received', $submission, $device->bloodCenter, [
                'device_id' => $device->id,
                'donation_identifier' => $data->donationIdentifier,
                'payload_hash' => $payloadHash,
            ]);

            return $submission;
        }, attempts: 3);
    }

    private function sequenceInBatch(OfflineCollectionDevice $device, OfflineIdentifierBatch $batch, string $identifier): ?int
    {
        if (! $this->identifierService->validate($device->bloodCenter, $identifier)) {
            return null;
        }

        for ($sequence = $batch->start_sequence; $sequence <= $batch->end_sequence; $sequence++) {
            if (hash_equals($this->identifierService->format($device->bloodCenter, $batch->year, $sequence), $identifier)) {
                return $sequence;
            }
        }

        return null;
    }
}
