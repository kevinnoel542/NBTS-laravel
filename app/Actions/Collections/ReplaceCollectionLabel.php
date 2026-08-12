<?php

namespace App\Actions\Collections;

use App\CollectionEpisodeStatus;
use App\CollectionLabelStatus;
use App\Models\CollectionLabel;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ReplaceCollectionLabel
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, CollectionLabel $collectionLabel, string $reason): CollectionLabel
    {
        return DB::transaction(function () use ($actor, $collectionLabel, $reason): CollectionLabel {
            $label = CollectionLabel::query()->with('collectionEpisode.bloodCenter')->lockForUpdate()->findOrFail($collectionLabel->id);
            Gate::forUser($actor)->authorize('manage', $label);

            if ($label->collectionEpisode->status !== CollectionEpisodeStatus::Prepared) {
                throw ValidationException::withMessages(['label' => ['A label cannot be replaced after collection has started. Record a controlled incident instead.']]);
            }

            if ($label->status === CollectionLabelStatus::Voided || mb_strlen(trim($reason)) < 10) {
                throw ValidationException::withMessages(['reason' => ['A current label and replacement reason of at least 10 characters are required.']]);
            }

            $replacementNumber = CollectionLabel::query()
                ->where('collection_episode_id', $label->collection_episode_id)
                ->where(fn ($query) => $label->specimen_id === null
                    ? $query->where('collection_container_id', $label->collection_container_id)->whereNull('specimen_id')
                    : $query->where('specimen_id', $label->specimen_id))
                ->count();
            $label->forceFill([
                'status' => CollectionLabelStatus::Voided,
                'voided_by' => $actor->id,
                'voided_at' => now(),
                'reason' => trim($reason),
            ])->save();
            $replacement = CollectionLabel::query()->create([
                'collection_episode_id' => $label->collection_episode_id,
                'collection_container_id' => $label->collection_container_id,
                'specimen_id' => $label->specimen_id,
                'label_identifier' => $label->label_identifier.'-R'.$replacementNumber,
                'symbology' => $label->symbology,
                'template_version' => $label->template_version,
                'status' => CollectionLabelStatus::Generated,
                'reason' => 'Replacement for label '.$label->id,
            ]);
            $this->auditLogger->record($actor, 'collection.label_replaced', $replacement, $label->collectionEpisode->bloodCenter, [
                'voided_label_id' => $label->id,
                'reason' => trim($reason),
            ]);

            return $replacement;
        }, attempts: 3);
    }
}
