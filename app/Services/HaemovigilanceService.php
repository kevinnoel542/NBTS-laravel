<?php

namespace App\Services;

use App\HaemovigilanceEventStatus;
use App\HaemovigilanceEventType;
use App\Models\CollectionEpisode;
use App\Models\HaemovigilanceEvent;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\PermissionName;
use App\QualitySeverity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class HaemovigilanceService
{
    public function __construct(private HospitalAccessService $hospitalAccess) {}

    /**
     * @param  list<string>  $symptoms
     * @param  array<string, mixed>  $context
     */
    public function recordDonorReaction(
        CollectionEpisode $episode,
        User $actor,
        QualitySeverity $severity,
        string $reactionType,
        array $symptoms,
        string $treatment,
        ?string $referral = null,
        array $context = [],
    ): HaemovigilanceEvent {
        if (! $actor->can(PermissionName::RecordDonorReactions->value) && ! $actor->can(PermissionName::ManageHaemovigilance->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot record donor haemovigilance events.']]);
        }

        if (! $actor->hasCenterAccess($episode->blood_center_id)) {
            throw ValidationException::withMessages(['center' => ['This account cannot access the selected center.']]);
        }

        return DB::transaction(function () use ($episode, $actor, $severity, $reactionType, $symptoms, $treatment, $referral, $context): HaemovigilanceEvent {
            return $this->createEvent([
                'blood_center_id' => $episode->blood_center_id,
                'donor_id' => $episode->donor_id,
                'event_type' => HaemovigilanceEventType::DonorReaction,
                'equipment_context' => [
                    'collection_episode_id' => $episode->id,
                    'device_identifier' => $episode->device_identifier,
                ],
                'reaction_type' => $reactionType,
                'referral' => $referral,
                'severity' => $severity,
                'supply_context' => [
                    'bag_lot' => $episode->bag_lot,
                    ...$context,
                ],
                'symptoms' => $symptoms,
                'treatment' => $treatment,
            ], $actor);
        }, attempts: 3);
    }

    /**
     * @param  list<string>  $symptoms
     */
    public function recordRecipientReaction(
        TransfusionRecord $transfusion,
        User $actor,
        QualitySeverity $severity,
        string $reactionType,
        array $symptoms,
        string $immediateAction,
        string $outcome,
        array $investigationContext = [],
        ?string $classification = null,
        ?string $imputability = null,
        string $reportingState = 'reported',
    ): HaemovigilanceEvent {
        if (! $actor->can(PermissionName::ManageHaemovigilance->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot record recipient haemovigilance events.']]);
        }

        $transfusion->loadMissing('bloodRequest.hospital', 'component');
        $this->hospitalAccess->ensure($actor, $transfusion->bloodRequest->hospital);

        return DB::transaction(function () use ($transfusion, $actor, $severity, $reactionType, $symptoms, $immediateAction, $outcome, $investigationContext, $classification, $imputability, $reportingState): HaemovigilanceEvent {
            return $this->createEvent([
                'blood_component_id' => $transfusion->blood_component_id,
                'hospital_blood_request_id' => $transfusion->hospital_blood_request_id,
                'hospital_id' => $transfusion->bloodRequest->hospital_id,
                'event_type' => HaemovigilanceEventType::RecipientReaction,
                'immediate_action' => $immediateAction,
                'classification' => $classification,
                'imputability' => $imputability,
                'investigation_context' => $investigationContext,
                'reaction_type' => $reactionType,
                'reporting_state' => trim($reportingState),
                'severity' => $severity,
                'symptoms' => $symptoms,
                'transfusion_record_id' => $transfusion->id,
                'outcome' => $outcome,
            ], $actor);
        }, attempts: 3);
    }

    public function close(HaemovigilanceEvent $event, User $actor, string $outcome): HaemovigilanceEvent
    {
        if (! $actor->can(PermissionName::ManageHaemovigilance->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot close haemovigilance events.']]);
        }

        if (mb_strlen(trim($outcome)) < 10) {
            throw ValidationException::withMessages(['outcome' => ['Closure requires an outcome summary.']]);
        }

        return DB::transaction(function () use ($event, $actor, $outcome): HaemovigilanceEvent {
            $record = HaemovigilanceEvent::query()->lockForUpdate()->findOrFail($event->id);
            $record->forceFill([
                'closed_at' => now(),
                'closed_by' => $actor->id,
                'outcome' => trim($outcome),
                'status' => HaemovigilanceEventStatus::Closed,
            ])->save();

            return $record->refresh();
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEvent(array $attributes, User $actor): HaemovigilanceEvent
    {
        $severity = $attributes['severity'];
        $isSerious = in_array($severity, [QualitySeverity::High, QualitySeverity::Critical], true);

        return HaemovigilanceEvent::query()->create([
            'assigned_to' => $isSerious ? $actor->id : null,
            'closed_by' => null,
            'event_reference' => 'HV-'.Str::upper(Str::random(10)),
            'followup_due_at' => now()->addDays($isSerious ? 1 : 7),
            'immediate_action' => $attributes['immediate_action'] ?? 'Recorded and assigned for review.',
            'notifications' => $isSerious ? [
                'center_or_hospital' => true,
                'nbts_quality_haemovigilance' => true,
                'national_authority' => $severity === QualitySeverity::Critical,
            ] : [],
            'occurred_at' => now(),
            'reported_by' => $actor->id,
            'status' => $isSerious ? HaemovigilanceEventStatus::Escalated : HaemovigilanceEventStatus::Open,
            'escalated_at' => $isSerious ? now() : null,
            ...$attributes,
        ]);
    }
}
