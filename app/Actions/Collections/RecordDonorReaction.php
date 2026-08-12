<?php

namespace App\Actions\Collections;

use App\DonorReactionSeverity;
use App\Models\CollectionEpisode;
use App\Models\DonorReaction;
use App\Models\User;
use App\PermissionName;
use App\Support\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class RecordDonorReaction
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param list<string> $symptoms */
    public function handle(
        User $actor,
        CollectionEpisode $collectionEpisode,
        DonorReactionSeverity $severity,
        string $reactionType,
        array $symptoms,
        CarbonInterface $occurredAt,
        ?string $treatment = null,
        ?string $referral = null,
        ?string $outcome = null,
        bool $followupRequired = false,
        ?CarbonInterface $followupDueAt = null,
    ): DonorReaction {
        Gate::forUser($actor)->authorize('view', $collectionEpisode);
        if (! $actor->can(PermissionName::RecordDonorReactions->value) || $symptoms === []) {
            throw ValidationException::withMessages(['reaction' => ['At least one observed symptom is required.']]);
        }

        return DB::transaction(function () use ($actor, $collectionEpisode, $severity, $reactionType, $symptoms, $occurredAt, $treatment, $referral, $outcome, $followupRequired, $followupDueAt): DonorReaction {
            $episode = CollectionEpisode::query()->with('bloodCenter')->lockForUpdate()->findOrFail($collectionEpisode->id);
            $reaction = DonorReaction::query()->create([
                'collection_episode_id' => $episode->id,
                'donor_id' => $episode->donor_id,
                'blood_center_id' => $episode->blood_center_id,
                'severity' => $severity,
                'reaction_type' => trim($reactionType),
                'symptoms' => $symptoms,
                'occurred_at' => $occurredAt,
                'treatment' => $treatment,
                'referral' => $referral,
                'outcome' => $outcome,
                'followup_required' => $followupRequired,
                'followup_due_at' => $followupDueAt,
                'recorded_by' => $actor->id,
            ]);
            $this->auditLogger->record($actor, 'collection.donor_reaction_recorded', $reaction, $episode->bloodCenter, [
                'severity' => $severity->value,
                'followup_required' => $followupRequired,
            ]);

            return $reaction;
        }, attempts: 3);
    }
}
