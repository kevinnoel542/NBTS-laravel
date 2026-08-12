<?php

namespace App\Actions\Offline;

use App\Models\OfflineCollectionDevice;
use App\Models\OfflineIdentifierBatch;
use App\Models\User;
use App\OfflineCollectionDeviceStatus;
use App\Services\CollectionIdentifierService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class IssueOfflineIdentifierBatch
{
    public function __construct(
        private CollectionIdentifierService $identifierService,
        private AuditLogger $auditLogger,
    ) {}

    public function handle(User $actor, OfflineCollectionDevice $device, ?int $size = null): OfflineIdentifierBatch
    {
        $device->loadMissing('bloodCenter');
        Gate::forUser($actor)->authorize('manageAt', [OfflineCollectionDevice::class, $device->bloodCenter]);
        $size ??= (int) config('phase-six.offline.identifier_batch_size', 50);

        if ($device->status !== OfflineCollectionDeviceStatus::Active || ! $device->bloodCenter->offline_collection_enabled) {
            throw ValidationException::withMessages(['device' => ['The device and center must be active for offline collection.']]);
        }

        return DB::transaction(function () use ($actor, $device, $size): OfflineIdentifierBatch {
            $range = $this->identifierService->reserve($device->bloodCenter, $size);
            $batch = OfflineIdentifierBatch::query()->create([
                'offline_collection_device_id' => $device->id,
                'blood_center_id' => $device->blood_center_id,
                'year' => $range['year'],
                'prefix' => $device->bloodCenter->collection_identifier_prefix,
                'start_sequence' => $range['start'],
                'end_sequence' => $range['end'],
                'next_sequence' => $range['start'],
                'issued_by' => $actor->id,
                'issued_at' => now(),
                'expires_at' => now()->addDays((int) config('phase-six.offline.identifier_batch_ttl_days', 7)),
            ]);
            $this->auditLogger->record($actor, 'offline.identifier_batch_issued', $batch, $device->bloodCenter, [
                'device_id' => $device->id,
                'range_size' => $size,
                'start_identifier' => $this->identifierService->format($device->bloodCenter, $range['year'], $range['start']),
                'end_identifier' => $this->identifierService->format($device->bloodCenter, $range['year'], $range['end']),
            ]);

            return $batch;
        }, attempts: 3);
    }
}
