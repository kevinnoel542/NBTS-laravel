<?php

namespace App\Services;

use App\EqaAssessmentStatus;
use App\Models\EqaAssessment;
use App\Models\Hospital;
use App\Models\HospitalTransfusionCommitteeReview;
use App\Models\QualityAudit;
use App\Models\QualityDeviation;
use App\Models\QualityDocument;
use App\Models\QualityTrainingRecord;
use App\Models\User;
use App\PermissionName;
use App\QualityAuditStatus;
use App\QualityDeviationStatus;
use App\QualityDocumentStatus;
use App\QualitySeverity;
use App\QualityTrainingStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class QualityManagementService
{
    /**
     * @param  array<int, array<string, mixed>>  $affectedRecords
     */
    public function openDeviation(User $actor, string $type, QualitySeverity $severity, string $title, string $description, array $affectedRecords = []): QualityDeviation
    {
        if (! $actor->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot open quality deviations.']]);
        }

        return QualityDeviation::query()->create([
            'affected_records' => $affectedRecords,
            'description' => trim($description),
            'deviation_reference' => 'DEV-'.Str::upper(Str::random(10)),
            'due_at' => now()->addDays($severity === QualitySeverity::Critical ? 2 : 14),
            'opened_at' => now(),
            'opened_by' => $actor->id,
            'owner_id' => $actor->id,
            'severity' => $severity,
            'status' => QualityDeviationStatus::Open,
            'title' => trim($title),
            'type' => trim($type),
        ]);
    }

    public function closeDeviation(QualityDeviation $deviation, User $qualityApprover, string $rootCause, string $correctiveAction, string $preventiveAction, string $effectivenessCheck, string $closureEvidence): QualityDeviation
    {
        if (! $qualityApprover->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot close quality deviations.']]);
        }

        if ($deviation->severity === QualitySeverity::Critical
            && (mb_strlen(trim($rootCause)) < 10
                || mb_strlen(trim($correctiveAction)) < 10
                || mb_strlen(trim($preventiveAction)) < 10
                || mb_strlen(trim($effectivenessCheck)) < 10
                || mb_strlen(trim($closureEvidence)) < 5)) {
            throw ValidationException::withMessages(['capa' => ['Critical CAPA closure requires RCA, corrective action, preventive action, effectiveness check, and evidence.']]);
        }

        return DB::transaction(function () use ($deviation, $qualityApprover, $rootCause, $correctiveAction, $preventiveAction, $effectivenessCheck, $closureEvidence): QualityDeviation {
            $record = QualityDeviation::query()->lockForUpdate()->findOrFail($deviation->id);
            $record->forceFill([
                'closed_at' => now(),
                'closed_by' => $qualityApprover->id,
                'closure_evidence' => trim($closureEvidence),
                'corrective_action' => trim($correctiveAction),
                'effectiveness_check' => trim($effectivenessCheck),
                'effectiveness_checked_at' => now(),
                'preventive_action' => trim($preventiveAction),
                'quality_approved_by' => $qualityApprover->id,
                'root_cause' => trim($rootCause),
                'status' => QualityDeviationStatus::Closed,
            ])->save();

            return $record->refresh();
        }, attempts: 3);
    }

    public function approveDocument(User $approver, string $code, int $version, string $title, string $type, array $workflows): QualityDocument
    {
        if (! $approver->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot approve quality documents.']]);
        }

        return QualityDocument::query()->create([
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'applies_to_workflows' => $workflows,
            'document_code' => trim($code),
            'document_type' => trim($type),
            'effective_from' => today(),
            'status' => QualityDocumentStatus::Effective,
            'title' => trim($title),
            'version' => $version,
        ]);
    }

    public function recordTraining(User $staff, User $verifier, QualityDocument $document, string $competencyCode): QualityTrainingRecord
    {
        if (! $verifier->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot verify training.']]);
        }

        return QualityTrainingRecord::query()->create([
            'competency_code' => trim($competencyCode),
            'evidence_reference' => 'TRN-'.Str::upper(Str::random(8)),
            'quality_document_id' => $document->id,
            'reassessment_due_at' => now()->addYear(),
            'retraining_required' => false,
            'status' => QualityTrainingStatus::Competent,
            'title' => $document->title,
            'trained_on' => today(),
            'user_id' => $staff->id,
            'valid_until' => today()->addYear(),
            'verified_by' => $verifier->id,
        ]);
    }

    public function startAudit(User $auditor, string $type, array $scope): QualityAudit
    {
        if (! $auditor->can(PermissionName::ViewAudits->value) || ! $auditor->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['Audit execution requires audit and quality authority.']]);
        }

        return QualityAudit::query()->create([
            'audit_reference' => 'AUD-'.Str::upper(Str::random(10)),
            'audit_type' => trim($type),
            'lead_auditor_id' => $auditor->id,
            'scheduled_on' => today(),
            'scope' => $scope,
            'started_at' => now(),
            'status' => QualityAuditStatus::InProgress,
        ]);
    }

    public function submitEqa(EqaAssessment $assessment, User $actor, array $submittedResults, array $expectedResults): EqaAssessment
    {
        if (! $actor->can(PermissionName::RecordLaboratoryTests->value) && ! $actor->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot submit EQA results.']]);
        }

        return DB::transaction(function () use ($assessment, $actor, $submittedResults, $expectedResults): EqaAssessment {
            $record = EqaAssessment::query()->lockForUpdate()->findOrFail($assessment->id);
            $acceptable = $submittedResults === $expectedResults;
            $record->forceFill([
                'expected_results' => $expectedResults,
                'findings' => $acceptable ? [] : ['nonconforming_results' => true],
                'reviewed_at' => now(),
                'reviewed_by' => $actor->id,
                'status' => $acceptable ? EqaAssessmentStatus::Acceptable : EqaAssessmentStatus::Nonconforming,
                'submitted_at' => now(),
                'submitted_by' => $actor->id,
                'submitted_results' => $submittedResults,
            ])->save();

            return $record->refresh();
        }, attempts: 3);
    }

    public function recordHospitalCommitteeReview(Hospital $hospital, User $chair, array $metrics, array $actions): HospitalTransfusionCommitteeReview
    {
        if (! $chair->can(PermissionName::ManageHaemovigilance->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot record transfusion committee review.']]);
        }

        return HospitalTransfusionCommitteeReview::query()->create([
            'chaired_by' => $chair->id,
            'closed_at' => now(),
            'education_actions' => $actions,
            'emergency_release_review' => $metrics['emergency_release'] ?? [],
            'hospital_id' => $hospital->id,
            'meeting_date' => today(),
            'reaction_review' => $metrics['reactions'] ?? [],
            'review_reference' => 'HTC-'.Str::upper(Str::random(10)),
            'status' => QualityAuditStatus::Closed,
            'utilization_metrics' => $metrics['utilization'] ?? [],
            'wastage_review' => $metrics['wastage'] ?? [],
        ]);
    }

    /**
     * @return array{
     *     repeated_deviation_types: array<string, int>,
     *     open_critical_count: int,
     *     overdue_open_count: int,
     *     audit_linked_deviation_ids: list<int>
     * }
     */
    public function deviationTrendSnapshot(User $actor, int $minimumRepeatCount = 2): array
    {
        if (! $actor->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot review quality trend analysis.']]);
        }

        /** @var Collection<int, QualityDeviation> $deviations */
        $deviations = QualityDeviation::query()->get(['id', 'type', 'severity', 'status', 'due_at']);

        $repeatedTypes = $deviations
            ->groupBy('type')
            ->map->count()
            ->filter(fn (int $count): bool => $count >= $minimumRepeatCount)
            ->all();

        /** @var Collection<int, QualityAudit> $audits */
        $audits = QualityAudit::query()->whereNotNull('linked_deviation_ids')->get(['linked_deviation_ids']);
        $auditLinkedDeviationIds = $audits
            ->flatMap(fn (QualityAudit $audit): array => $audit->linked_deviation_ids ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        return [
            'audit_linked_deviation_ids' => $auditLinkedDeviationIds,
            'open_critical_count' => $deviations
                ->filter(fn (QualityDeviation $deviation): bool => $deviation->severity === QualitySeverity::Critical
                    && $deviation->status !== QualityDeviationStatus::Closed)
                ->count(),
            'overdue_open_count' => $deviations
                ->filter(fn (QualityDeviation $deviation): bool => $deviation->due_at !== null
                    && $deviation->due_at->isPast()
                    && $deviation->status !== QualityDeviationStatus::Closed)
                ->count(),
            'repeated_deviation_types' => $repeatedTypes,
        ];
    }
}
