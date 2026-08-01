<?php

namespace App\Actions\Centers;

use App\Models\BloodCenter;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class SaveBloodCenter
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data, ?BloodCenter $bloodCenter = null): BloodCenter
    {
        Gate::forUser($actor)->authorize(
            $bloodCenter === null ? 'create' : 'update',
            $bloodCenter ?? BloodCenter::class,
        );

        return DB::transaction(function () use ($actor, $data, $bloodCenter): BloodCenter {
            $center = $bloodCenter === null
                ? new BloodCenter
                : BloodCenter::query()->lockForUpdate()->findOrFail($bloodCenter->id);
            $wasRecentlyCreated = ! $center->exists;

            $center->fill(Arr::only($data, [
                'name',
                'address',
                'city',
                'phone',
                'email',
                'opening_hours',
                'services',
                'capacity_label',
                'estimated_wait_minutes',
                'center_type',
                'image_path',
                'latitude',
                'longitude',
                'is_active',
            ]))->save();

            $this->auditLogger->record(
                actor: $actor,
                action: $wasRecentlyCreated ? 'blood_center.created' : 'blood_center.updated',
                subject: $center,
                bloodCenter: $center,
                metadata: [
                    'changed_fields' => array_keys($data),
                    'is_active' => $center->is_active,
                ],
            );

            return $center->refresh();
        }, attempts: 3);
    }
}
