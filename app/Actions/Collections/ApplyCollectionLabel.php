<?php

namespace App\Actions\Collections;

use App\CollectionLabelStatus;
use App\Models\CollectionLabel;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ApplyCollectionLabel
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, CollectionLabel $collectionLabel, string $scannedIdentifier): CollectionLabel
    {
        return DB::transaction(function () use ($actor, $collectionLabel, $scannedIdentifier): CollectionLabel {
            $label = CollectionLabel::query()->with('collectionEpisode.bloodCenter')->lockForUpdate()->findOrFail($collectionLabel->id);
            Gate::forUser($actor)->authorize('manage', $label);

            if ($label->status !== CollectionLabelStatus::Printed) {
                throw ValidationException::withMessages(['label' => ['Print the current label before applying it.']]);
            }

            if (! hash_equals($label->label_identifier, trim($scannedIdentifier))) {
                throw ValidationException::withMessages(['scanned_identifier' => ['Scan mismatch: the label was not applied.']]);
            }

            $label->forceFill([
                'status' => CollectionLabelStatus::Applied,
                'applied_by' => $actor->id,
                'applied_at' => now(),
            ])->save();
            $this->auditLogger->record($actor, 'collection.label_applied', $label, $label->collectionEpisode->bloodCenter, [
                'identifier' => $label->label_identifier,
            ]);

            return $label;
        }, attempts: 3);
    }
}
