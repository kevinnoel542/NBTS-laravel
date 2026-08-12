<?php

namespace App\Actions\Collections;

use App\CollectionEpisodeStatus;
use App\CollectionLabelStatus;
use App\Models\Specimen;
use App\Models\User;
use App\SpecimenStatus;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class CollectSpecimen
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, Specimen $specimen, string $scannedIdentifier, float $volumeMl): Specimen
    {
        return DB::transaction(function () use ($actor, $specimen, $scannedIdentifier, $volumeMl): Specimen {
            $record = Specimen::query()->with(['collectionEpisode.bloodCenter', 'labels'])->lockForUpdate()->findOrFail($specimen->id);
            Gate::forUser($actor)->authorize('update', $record->collectionEpisode);
            $label = $record->labels->firstWhere('status', CollectionLabelStatus::Applied);

            if ($record->collectionEpisode->status !== CollectionEpisodeStatus::InProgress
                || $record->status !== SpecimenStatus::Expected
                || $label === null
                || ! hash_equals($label->label_identifier, trim($scannedIdentifier))
                || $volumeMl <= 0 || $volumeMl > 50) {
                throw ValidationException::withMessages(['specimen' => ['The specimen, applied label, scan, status, or volume is invalid.']]);
            }

            $record->forceFill(['status' => SpecimenStatus::Collected, 'volume_ml' => $volumeMl, 'collected_by' => $actor->id, 'collected_at' => now()])->save();
            $this->auditLogger->record($actor, 'collection.specimen_collected', $record, $record->collectionEpisode->bloodCenter, [
                'identifier' => $record->specimen_identifier,
                'volume_ml' => $volumeMl,
            ]);

            return $record;
        }, attempts: 3);
    }
}
