<?php

namespace App\Actions\Collections;

use App\CollectionLabelStatus;
use App\Models\CollectionLabel;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class PrintCollectionLabel
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, CollectionLabel $collectionLabel, string $printerName, ?string $reason = null): CollectionLabel
    {
        return DB::transaction(function () use ($actor, $collectionLabel, $printerName, $reason): CollectionLabel {
            $label = CollectionLabel::query()->with('collectionEpisode.bloodCenter')->lockForUpdate()->findOrFail($collectionLabel->id);
            Gate::forUser($actor)->authorize('manage', $label);

            if (! in_array($label->status, [CollectionLabelStatus::Generated, CollectionLabelStatus::Printed], true)) {
                throw ValidationException::withMessages(['label' => ['Only a generated or previously printed label may be printed.']]);
            }

            if ($label->print_count > 0 && mb_strlen(trim((string) $reason)) < 10) {
                throw ValidationException::withMessages(['reason' => ['A reprint reason of at least 10 characters is required.']]);
            }

            $label->forceFill([
                'status' => CollectionLabelStatus::Printed,
                'print_count' => $label->print_count + 1,
                'printer_name' => trim($printerName),
                'printed_by' => $actor->id,
                'printed_at' => now(),
                'reason' => $reason,
            ])->save();
            $this->auditLogger->record($actor, 'collection.label_printed', $label, $label->collectionEpisode->bloodCenter, [
                'identifier' => $label->label_identifier,
                'print_count' => $label->print_count,
                'reprint' => $label->print_count > 1,
            ]);

            return $label;
        }, attempts: 3);
    }
}
