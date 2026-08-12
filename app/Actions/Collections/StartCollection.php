<?php

namespace App\Actions\Collections;

use App\CollectionEpisodeStatus;
use App\CollectionLabelStatus;
use App\Models\CollectionEpisode;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class StartCollection
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, CollectionEpisode $collectionEpisode): CollectionEpisode
    {
        return DB::transaction(function () use ($actor, $collectionEpisode): CollectionEpisode {
            $episode = CollectionEpisode::query()->with(['bloodCenter', 'labels'])->lockForUpdate()->findOrFail($collectionEpisode->id);
            Gate::forUser($actor)->authorize('update', $episode);

            $currentLabels = $episode->labels
                ->where('status', '!=', CollectionLabelStatus::Voided);

            if ($episode->status !== CollectionEpisodeStatus::Prepared
                || $currentLabels->isEmpty()
                || $currentLabels->contains(fn ($label): bool => $label->status !== CollectionLabelStatus::Applied)) {
                throw ValidationException::withMessages(['collection' => ['Apply every current container and specimen label before collection starts.']]);
            }

            $episode->forceFill(['status' => CollectionEpisodeStatus::InProgress, 'started_at' => now(), 'collected_by' => $actor->id])->save();
            $this->auditLogger->record($actor, 'collection.started', $episode, $episode->bloodCenter, []);

            return $episode;
        }, attempts: 3);
    }
}
