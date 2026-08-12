<?php

namespace App\Actions\Offline;

use App\Models\BloodCenter;
use App\Models\OfflineCollectionDevice;
use App\Models\User;
use App\OfflineCollectionDeviceStatus;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class RegisterOfflineCollectionDevice
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @return array{device: OfflineCollectionDevice, credential: string} */
    public function handle(User $actor, BloodCenter $bloodCenter, User $assignee, string $name): array
    {
        Gate::forUser($actor)->authorize('manageAt', [OfflineCollectionDevice::class, $bloodCenter]);
        if (! $bloodCenter->offline_collection_enabled || ! $assignee->hasCenterAccess($bloodCenter)) {
            throw ValidationException::withMessages(['device' => ['Offline collection must be enabled and the assignee must have center access.']]);
        }

        return DB::transaction(function () use ($actor, $bloodCenter, $assignee, $name): array {
            $credential = Str::random(64);
            $device = OfflineCollectionDevice::query()->create([
                'device_uuid' => (string) Str::uuid(),
                'blood_center_id' => $bloodCenter->id,
                'assigned_to' => $assignee->id,
                'name' => trim($name),
                'status' => OfflineCollectionDeviceStatus::Active,
                'credential_fingerprint' => hash('sha256', $credential),
            ]);
            $this->auditLogger->record($actor, 'offline.device_registered', $device, $bloodCenter, ['assigned_to' => $assignee->id]);

            return ['device' => $device, 'credential' => $credential];
        }, attempts: 3);
    }

    public function revoke(User $actor, OfflineCollectionDevice $device, string $reason): OfflineCollectionDevice
    {
        Gate::forUser($actor)->authorize('update', $device);
        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages(['reason' => ['A revocation reason of at least 10 characters is required.']]);
        }

        return DB::transaction(function () use ($actor, $device, $reason): OfflineCollectionDevice {
            $record = OfflineCollectionDevice::query()->with('bloodCenter')->lockForUpdate()->findOrFail($device->id);
            $record->forceFill([
                'status' => OfflineCollectionDeviceStatus::Revoked,
                'revoked_at' => now(),
                'revoked_by' => $actor->id,
                'revocation_reason' => trim($reason),
            ])->save();
            $record->identifierBatches()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $this->auditLogger->record($actor, 'offline.device_revoked', $record, $record->bloodCenter, ['reason' => trim($reason)]);

            return $record;
        }, attempts: 3);
    }
}
