<?php

namespace App\Services;

use App\Models\BloodCenter;
use App\Models\RolloutPilotReadinessReview;
use App\Models\RolloutPolicyDecision;
use App\Models\RolloutScaleReadinessReview;
use App\Models\RolloutSiteAssessment;
use App\Models\User;
use App\PermissionName;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PhaseThirteenRolloutService
{
    private const REQUIRED_WORKFLOWS = [
        'adverse_event',
        'collection',
        'components',
        'downtime',
        'governance',
        'hospital',
        'laboratory',
        'logistics',
        'recall',
        'storage',
        'transfusion',
    ];

    private const REQUIRED_INVENTORY_AREAS = [
        'analyzers',
        'budgets',
        'centers',
        'connectivity',
        'contracts',
        'equipment',
        'forms',
        'hospitals',
        'identifiers',
        'integrations',
        'laws',
        'power',
        'reagents',
        'routes',
        'sensors',
        'sops',
        'staff',
        'storage',
        'volumes',
    ];

    private const REQUIRED_KPI_BASELINES = [
        'adoption',
        'downtime',
        'expiry',
        'incident',
        'request_fill',
        'safety',
        'support',
        'turnaround',
    ];

    private const REQUIRED_POLICY_CATEGORIES = [
        'barcode_standard',
        'center_taxonomy',
        'compatibility_emergency',
        'component_catalog',
        'identifier_standard',
        'integrations',
        'offline_mode',
        'patient_data',
        'release_authority',
        'retention',
        'rto_rpo',
        'service_levels',
        'shelf_lives',
        'test_algorithms',
    ];

    private const REQUIRED_PILOT_CHAIN = [
        'collection',
        'compatibility',
        'components',
        'dispatch_receipt',
        'hospital_request',
        'inventory',
        'laboratory_qc',
        'quarantine_release',
        'screening',
        'specimens',
        'transfusion_outcome',
    ];

    private const REQUIRED_PILOT_PREREQUISITES = [
        'approved_policy',
        'hardware_ready',
        'sop_deployed',
        'support_ready',
        'test_environment',
    ];

    private const REQUIRED_SIGNOFFS = [
        'clinical',
        'quality',
        'operations',
    ];

    private const REQUIRED_BUDGET_AREAS = [
        'backups',
        'calibration',
        'connectivity',
        'devices',
        'infrastructure',
        'maintenance',
        'messaging',
        'monitoring',
        'security',
        'sensors',
        'support',
        'training',
        'validation',
    ];

    private const REQUIRED_VENDOR_EXIT_AREAS = [
        'data_exports',
        'deployment_automation',
        'documentation',
        'handover_drill',
        'independent_recovery',
        'local_administrators',
        'source_ownership',
        'test_suites',
    ];

    /**
     * @param  list<string>  $workflowMap
     * @param  array<string, mixed>  $inventorySnapshot
     * @param  array<string, mixed>  $baselineKpis
     * @param  list<array<string, mixed>>  $risks
     * @param  list<string>  $dataDictionaryScope
     * @param  array<string, string>  $masterDataOwners
     * @param  array<string, mixed>  $pilotScope
     * @param  array<string, mixed>  $prioritizedBacklog
     * @param  list<string>  $legalAndPolicyInputs
     * @param  array<string, mixed>  $operationalReadiness
     */
    public function recordSiteAssessment(
        User $actor,
        ?BloodCenter $bloodCenter,
        string $siteName,
        string $siteType,
        array $workflowMap,
        array $inventorySnapshot,
        array $baselineKpis,
        array $risks,
        array $dataDictionaryScope,
        array $masterDataOwners,
        ?string $safetyCaseReference,
        ?string $targetProcessReference,
        array $pilotScope,
        array $prioritizedBacklog,
        array $legalAndPolicyInputs,
        array $operationalReadiness,
        bool $approve = false,
    ): RolloutSiteAssessment {
        $this->ensureCan($actor, PermissionName::ManageRollout);
        $this->assertCompleteDiscovery($workflowMap, $inventorySnapshot, $baselineKpis, $risks, $dataDictionaryScope, $masterDataOwners, $pilotScope, $prioritizedBacklog, $legalAndPolicyInputs);

        return RolloutSiteAssessment::query()->create([
            'approved_at' => $approve ? now() : null,
            'approved_by' => $approve ? $actor->id : null,
            'assessed_at' => now(),
            'assessed_by' => $actor->id,
            'assessment_reference' => 'RSA-'.Str::upper(Str::random(10)),
            'baseline_kpis' => $baselineKpis,
            'blood_center_id' => $bloodCenter?->id,
            'data_dictionary_scope' => array_values($dataDictionaryScope),
            'inventory_snapshot' => $inventorySnapshot,
            'legal_and_policy_inputs' => array_values($legalAndPolicyInputs),
            'master_data_owners' => $masterDataOwners,
            'operational_readiness' => $operationalReadiness,
            'pilot_scope' => $pilotScope,
            'prioritized_backlog' => $prioritizedBacklog,
            'risks' => $risks,
            'safety_case_reference' => $safetyCaseReference,
            'site_name' => trim($siteName),
            'site_type' => Str::slug($siteType, '_'),
            'status' => $approve ? 'approved' : 'review',
            'target_process_reference' => $targetProcessReference,
            'workflow_map' => array_values($workflowMap),
        ]);
    }

    /**
     * @param  list<string>  $optionsConsidered
     * @param  list<string>  $requiredApprovals
     * @param  array<string, mixed>|null  $approvalEvidence
     * @param  array<string, mixed>|null  $riskAcceptance
     * @param  list<string>  $implementationControls
     * @param  array<string, mixed>  $reviewSchedule
     */
    public function registerPolicyDecision(
        User $actor,
        ?RolloutSiteAssessment $siteAssessment,
        string $decisionCode,
        string $category,
        string $title,
        string $decisionSummary,
        array $optionsConsidered,
        array $requiredApprovals,
        ?array $approvalEvidence,
        ?array $riskAcceptance,
        array $implementationControls,
        array $reviewSchedule,
        string $status = 'pending',
    ): RolloutPolicyDecision {
        $this->ensureCan($actor, PermissionName::ManageRollout);
        $normalizedStatus = Str::slug($status, '_');
        $normalizedCategory = Str::slug($category, '_');

        if (! in_array($normalizedCategory, self::REQUIRED_POLICY_CATEGORIES, true)) {
            throw ValidationException::withMessages(['category' => ['This rollout policy category is not part of the Phase 13 approval set.']]);
        }

        if ($optionsConsidered === [] || $requiredApprovals === [] || $implementationControls === []) {
            throw ValidationException::withMessages(['decision' => ['Policy decisions require options, required approvals, and implementation controls.']]);
        }

        if ($normalizedStatus === 'approved' && ($approvalEvidence === null || $riskAcceptance === null)) {
            throw ValidationException::withMessages(['approval' => ['Approved policy decisions require approval evidence and risk acceptance.']]);
        }

        return RolloutPolicyDecision::query()->updateOrCreate(
            ['decision_code' => Str::upper($decisionCode)],
            [
                'approval_evidence' => $approvalEvidence,
                'approved_at' => $normalizedStatus === 'approved' ? now() : null,
                'approved_by' => $normalizedStatus === 'approved' ? $actor->id : null,
                'category' => $normalizedCategory,
                'decision_summary' => trim($decisionSummary),
                'due_at' => now()->addMonth(),
                'implementation_controls' => array_values($implementationControls),
                'options_considered' => array_values($optionsConsidered),
                'owner_id' => $actor->id,
                'required_approvals' => array_values($requiredApprovals),
                'review_schedule' => $reviewSchedule,
                'risk_acceptance' => $riskAcceptance,
                'rollout_site_assessment_id' => $siteAssessment?->id,
                'status' => $normalizedStatus,
                'title' => trim($title),
            ],
        );
    }

    /**
     * @param  list<RolloutPolicyDecision>  $policyDecisions
     * @param  list<string>  $pilotSites
     * @param  list<string>  $chainCoverage
     * @param  list<string>  $prerequisites
     * @param  array<string, mixed>  $validationEvidence
     * @param  array<string, mixed>  $dataMigrationEvidence
     * @param  array<string, mixed>  $trainingEvidence
     * @param  array<string, mixed>  $downtimeRestoreEvidence
     * @param  array<string, mixed>  $traceabilityRecallEvidence
     * @param  list<array<string, mixed>>  $openDefects
     * @param  list<string>  $signoffs
     * @param  array<string, mixed>  $exitCriteria
     */
    public function reviewPilotReadiness(
        User $actor,
        RolloutSiteAssessment $siteAssessment,
        array $policyDecisions,
        string $pilotName,
        array $pilotSites,
        array $chainCoverage,
        array $prerequisites,
        array $validationEvidence,
        array $dataMigrationEvidence,
        array $trainingEvidence,
        array $downtimeRestoreEvidence,
        array $traceabilityRecallEvidence,
        array $openDefects,
        array $signoffs,
        array $exitCriteria,
    ): RolloutPilotReadinessReview {
        $this->ensureCan($actor, PermissionName::ManageRollout);

        $blockedReasons = array_merge(
            $this->missingKeys('chain_coverage', self::REQUIRED_PILOT_CHAIN, $chainCoverage),
            $this->missingKeys('prerequisites', self::REQUIRED_PILOT_PREREQUISITES, $prerequisites),
            $this->missingKeys('signoffs', self::REQUIRED_SIGNOFFS, $signoffs),
            $this->pilotEvidenceProblems($siteAssessment, $policyDecisions, $validationEvidence, $dataMigrationEvidence, $trainingEvidence, $downtimeRestoreEvidence, $traceabilityRecallEvidence, $openDefects, $exitCriteria),
        );
        $ready = $blockedReasons === [];

        return RolloutPilotReadinessReview::query()->create([
            'approved_at' => $ready ? now() : null,
            'approved_by' => $ready ? $actor->id : null,
            'chain_coverage' => array_values($chainCoverage),
            'data_migration_evidence' => $dataMigrationEvidence,
            'downtime_restore_evidence' => $downtimeRestoreEvidence,
            'exit_criteria' => array_merge($exitCriteria, ['blocked_reasons' => $blockedReasons]),
            'open_defects' => $openDefects,
            'pilot_name' => trim($pilotName),
            'pilot_sites' => array_values($pilotSites),
            'prerequisites' => array_values($prerequisites),
            'review_reference' => 'RPR-'.Str::upper(Str::random(10)),
            'reviewed_at' => now(),
            'reviewed_by' => $actor->id,
            'rollout_site_assessment_id' => $siteAssessment->id,
            'signoffs' => array_values($signoffs),
            'status' => $ready ? 'ready' : 'blocked',
            'traceability_recall_evidence' => $traceabilityRecallEvidence,
            'training_evidence' => $trainingEvidence,
            'validation_evidence' => $validationEvidence,
        ]);
    }

    /**
     * @param  list<string>  $candidateSites
     * @param  list<string>  $readinessCriteria
     * @param  array<string, mixed>  $kpiComparison
     * @param  array<string, mixed>  $monitoringPlan
     * @param  array<string, mixed>  $supportModel
     * @param  list<string>  $operatingBudget
     * @param  list<string>  $vendorExitPlan
     * @param  list<array<string, mixed>>  $unresolvedRisks
     */
    public function reviewScaleReadiness(
        User $actor,
        RolloutPilotReadinessReview $pilotReadinessReview,
        string $scaleLevel,
        array $candidateSites,
        array $readinessCriteria,
        array $kpiComparison,
        array $monitoringPlan,
        array $supportModel,
        array $operatingBudget,
        array $vendorExitPlan,
        array $unresolvedRisks,
    ): RolloutScaleReadinessReview {
        $this->ensureCan($actor, PermissionName::ManageRollout);

        $normalizedScaleLevel = Str::slug($scaleLevel, '_');

        if (! in_array($normalizedScaleLevel, ['regional', 'national'], true)) {
            throw ValidationException::withMessages(['scale_level' => ['Scale level must be regional or national.']]);
        }

        $blockedReasons = array_merge(
            $pilotReadinessReview->status === 'ready' ? [] : ['pilot_readiness: Pilot must be ready before scale review can pass.'],
            $candidateSites === [] ? ['candidate_sites: At least one candidate site is required.'] : [],
            $readinessCriteria === [] ? ['readiness_criteria: Readiness criteria are required.'] : [],
            $this->missingKeys('kpi_comparison', self::REQUIRED_KPI_BASELINES, array_keys($kpiComparison)),
            $monitoringPlan === [] ? ['monitoring_plan: Monitoring plan is required.'] : [],
            $supportModel === [] ? ['support_model: Support model is required.'] : [],
            $this->criticalRiskProblems($unresolvedRisks),
        );

        if ($normalizedScaleLevel === 'national') {
            $blockedReasons = array_merge(
                $blockedReasons,
                $this->missingKeys('operating_budget', self::REQUIRED_BUDGET_AREAS, $operatingBudget),
                $this->missingKeys('vendor_exit_plan', self::REQUIRED_VENDOR_EXIT_AREAS, $vendorExitPlan),
            );
        }

        $ready = $blockedReasons === [];

        return RolloutScaleReadinessReview::query()->create([
            'approved_at' => $ready ? now() : null,
            'approved_by' => $ready ? $actor->id : null,
            'candidate_sites' => array_values($candidateSites),
            'kpi_comparison' => $kpiComparison,
            'monitoring_plan' => array_merge($monitoringPlan, ['blocked_reasons' => $blockedReasons]),
            'operating_budget' => array_values($operatingBudget),
            'readiness_criteria' => array_values($readinessCriteria),
            'review_reference' => 'RSR-'.Str::upper(Str::random(10)),
            'reviewed_at' => now(),
            'reviewed_by' => $actor->id,
            'rollout_pilot_readiness_review_id' => $pilotReadinessReview->id,
            'scale_level' => $normalizedScaleLevel,
            'status' => $ready ? 'ready' : 'blocked',
            'support_model' => $supportModel,
            'unresolved_risks' => $unresolvedRisks,
            'vendor_exit_plan' => array_values($vendorExitPlan),
        ]);
    }

    private function ensureCan(User $actor, PermissionName $permission): void
    {
        if (! $actor->can($permission->value)) {
            throw new AuthorizationException('This action is not permitted for rollout management.');
        }
    }

    /**
     * @param  list<string>  $workflowMap
     * @param  array<string, mixed>  $inventorySnapshot
     * @param  array<string, mixed>  $baselineKpis
     * @param  list<array<string, mixed>>  $risks
     * @param  list<string>  $dataDictionaryScope
     * @param  array<string, string>  $masterDataOwners
     * @param  array<string, mixed>  $pilotScope
     * @param  array<string, mixed>  $prioritizedBacklog
     * @param  list<string>  $legalAndPolicyInputs
     */
    private function assertCompleteDiscovery(
        array $workflowMap,
        array $inventorySnapshot,
        array $baselineKpis,
        array $risks,
        array $dataDictionaryScope,
        array $masterDataOwners,
        array $pilotScope,
        array $prioritizedBacklog,
        array $legalAndPolicyInputs,
    ): void {
        $problems = array_merge(
            $this->missingKeys('workflow_map', self::REQUIRED_WORKFLOWS, $workflowMap),
            $this->missingKeys('inventory_snapshot', self::REQUIRED_INVENTORY_AREAS, array_keys($inventorySnapshot)),
            $this->missingKeys('baseline_kpis', self::REQUIRED_KPI_BASELINES, array_keys($baselineKpis)),
        );

        if ($risks === []) {
            $problems[] = 'risks: Risk register input is required.';
        }

        if ($dataDictionaryScope === [] || $masterDataOwners === []) {
            $problems[] = 'data: Data dictionary scope and master-data ownership are required.';
        }

        if ($pilotScope === [] || $prioritizedBacklog === []) {
            $problems[] = 'pilot: Pilot scope and prioritized backlog are required.';
        }

        if ($legalAndPolicyInputs === []) {
            $problems[] = 'policy: Legal and policy inputs are required.';
        }

        if ($problems !== []) {
            throw ValidationException::withMessages(['discovery' => $problems]);
        }
    }

    /**
     * @param  list<string>  $required
     * @param  list<string>  $actual
     * @return list<string>
     */
    private function missingKeys(string $field, array $required, array $actual): array
    {
        $actual = array_map(static fn (string $value): string => Str::slug($value, '_'), $actual);

        return array_values(array_map(
            static fn (string $value): string => "{$field}: Missing {$value}.",
            array_diff($required, $actual),
        ));
    }

    /**
     * @param  list<RolloutPolicyDecision>  $policyDecisions
     * @param  array<string, mixed>  $validationEvidence
     * @param  array<string, mixed>  $dataMigrationEvidence
     * @param  array<string, mixed>  $trainingEvidence
     * @param  array<string, mixed>  $downtimeRestoreEvidence
     * @param  array<string, mixed>  $traceabilityRecallEvidence
     * @param  list<array<string, mixed>>  $openDefects
     * @param  array<string, mixed>  $exitCriteria
     * @return list<string>
     */
    private function pilotEvidenceProblems(
        RolloutSiteAssessment $siteAssessment,
        array $policyDecisions,
        array $validationEvidence,
        array $dataMigrationEvidence,
        array $trainingEvidence,
        array $downtimeRestoreEvidence,
        array $traceabilityRecallEvidence,
        array $openDefects,
        array $exitCriteria,
    ): array {
        $problems = [];

        if ($siteAssessment->status !== 'approved') {
            $problems[] = 'site_assessment: Discovery assessment must be approved before pilot readiness can pass.';
        }

        if (count($policyDecisions) < count(self::REQUIRED_POLICY_CATEGORIES)) {
            $problems[] = 'policy_decisions: Every required policy category must be represented.';
        }

        $approvedCategories = collect($policyDecisions)
            ->filter(fn (RolloutPolicyDecision $decision): bool => $decision->status === 'approved')
            ->pluck('category')
            ->map(fn (string $category): string => Str::slug($category, '_'))
            ->all();

        $problems = array_merge($problems, $this->missingKeys('policy_decisions', self::REQUIRED_POLICY_CATEGORIES, $approvedCategories));

        foreach ([
            'validation_evidence' => $validationEvidence,
            'data_migration_evidence' => $dataMigrationEvidence,
            'training_evidence' => $trainingEvidence,
            'downtime_restore_evidence' => $downtimeRestoreEvidence,
            'traceability_recall_evidence' => $traceabilityRecallEvidence,
        ] as $field => $evidence) {
            if ($evidence === [] || in_array(false, Arr::flatten($evidence), true)) {
                $problems[] = "{$field}: Evidence is missing or contains a failed check.";
            }
        }

        foreach ($openDefects as $defect) {
            if (($defect['severity'] ?? null) === 'critical') {
                $problems[] = 'open_defects: Critical defects must be resolved before pilot exit.';
            }
        }

        if (($exitCriteria['critical_defects'] ?? 1) !== 0) {
            $problems[] = 'exit_criteria: Critical defect count must be zero.';
        }

        return $problems;
    }

    /**
     * @param  list<array<string, mixed>>  $unresolvedRisks
     * @return list<string>
     */
    private function criticalRiskProblems(array $unresolvedRisks): array
    {
        foreach ($unresolvedRisks as $risk) {
            if (($risk['level'] ?? null) === 'critical') {
                return ['unresolved_risks: Critical rollout risks must be closed or formally accepted outside the system before scaling.'];
            }
        }

        return [];
    }
}
