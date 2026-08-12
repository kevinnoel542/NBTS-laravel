<?php

namespace App\Actions\Collections;

use App\Models\Specimen;
use App\Models\User;
use App\PermissionName;
use App\SpecimenStatus;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class HandOffSpecimen
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, Specimen $specimen, string $recipient): Specimen
    {
        return DB::transaction(function () use ($actor, $specimen, $recipient): Specimen {
            $record = Specimen::query()->with('collectionEpisode.bloodCenter')->lockForUpdate()->findOrFail($specimen->id);
            Gate::forUser($actor)->authorize('view', $record->collectionEpisode);

            if (! $actor->can(PermissionName::HandOffSpecimens->value)
                || $record->status !== SpecimenStatus::Collected
                || mb_strlen(trim($recipient)) < 3) {
                throw ValidationException::withMessages(['handoff' => ['A collected specimen and named receiving station are required.']]);
            }

            $record->forceFill(['status' => SpecimenStatus::HandedOff, 'handed_off_by' => $actor->id, 'handed_off_at' => now(), 'handoff_recipient' => trim($recipient)])->save();
            $this->auditLogger->record($actor, 'collection.specimen_handed_off', $record, $record->collectionEpisode->bloodCenter, [
                'identifier' => $record->specimen_identifier,
                'recipient' => trim($recipient),
            ]);

            return $record;
        }, attempts: 3);
    }
}
