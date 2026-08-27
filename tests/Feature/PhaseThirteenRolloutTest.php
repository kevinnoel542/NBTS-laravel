<?php

use App\Models\BloodCenter;
use App\Models\RolloutPilotReadinessReview;
use App\Models\RolloutPolicyDecision;
use App\Models\RolloutScaleReadinessReview;
use App\Models\RolloutSiteAssessment;
use App\Models\User;
use App\RoleName;
use App\Services\PhaseThirteenRolloutService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('discovery assessment requires complete workflow inventory kpi and governance inputs', function () {
    $actor = phaseThirteenActor(RoleName::NationalOperationsAdministrator);
    $service = app(PhaseThirteenRolloutService::class);

    expect(fn () => $service->recordSiteAssessment(
        actor: $actor,
        bloodCenter: null,
        siteName: 'Incomplete pilot site',
        siteType: 'blood center',
        workflowMap: ['collection'],
        inventorySnapshot: ['centers' => ['Muhimbili']],
        baselineKpis: ['safety' => 1],
        risks: [],
        dataDictionaryScope: [],
        masterDataOwners: [],
        safetyCaseReference: null,
        targetProcessReference: null,
        pilotScope: [],
        prioritizedBacklog: [],
        legalAndPolicyInputs: [],
        operationalReadiness: [],
    ))->toThrow(ValidationException::class);

    $assessment = phaseThirteenApprovedAssessment($actor, $service);

    expect($assessment)->toBeInstanceOf(RolloutSiteAssessment::class)
        ->and($assessment->status)->toBe('approved')
        ->and($assessment->workflow_map)->toContain('laboratory')
        ->and($assessment->inventory_snapshot)->toHaveKey('connectivity')
        ->and($assessment->baseline_kpis)->toHaveKeys(['safety', 'downtime', 'support'])
        ->and($assessment->approved_by)->toBe($actor->id);
});

test('policy decisions require approved categories evidence controls and risk acceptance', function () {
    $actor = phaseThirteenActor(RoleName::NationalOperationsAdministrator);
    $service = app(PhaseThirteenRolloutService::class);
    $assessment = phaseThirteenApprovedAssessment($actor, $service);

    expect(fn () => $service->registerPolicyDecision(
        actor: $actor,
        siteAssessment: $assessment,
        decisionCode: 'P13-BAD-CATEGORY',
        category: 'untracked decision',
        title: 'Bad decision',
        decisionSummary: 'Invalid policy area.',
        optionsConsidered: ['one'],
        requiredApprovals: ['operations'],
        approvalEvidence: ['approved' => true],
        riskAcceptance: ['accepted' => true],
        implementationControls: ['change_control'],
        reviewSchedule: ['frequency' => 'monthly'],
        status: 'approved',
    ))->toThrow(ValidationException::class);

    expect(fn () => $service->registerPolicyDecision(
        actor: $actor,
        siteAssessment: $assessment,
        decisionCode: 'P13-NO-EVIDENCE',
        category: 'identifier_standard',
        title: 'Identifier standard',
        decisionSummary: 'Missing approval evidence.',
        optionsConsidered: ['legacy', 'target'],
        requiredApprovals: ['operations', 'quality'],
        approvalEvidence: null,
        riskAcceptance: null,
        implementationControls: ['change_control'],
        reviewSchedule: ['frequency' => 'monthly'],
        status: 'approved',
    ))->toThrow(ValidationException::class);

    $decision = phaseThirteenApprovedDecision($actor, $service, $assessment, 'identifier_standard');
    $duplicate = phaseThirteenApprovedDecision($actor, $service, $assessment, 'identifier_standard');

    expect($decision)->toBeInstanceOf(RolloutPolicyDecision::class)
        ->and($decision->id)->toBe($duplicate->id)
        ->and($decision->status)->toBe('approved')
        ->and($decision->approval_evidence['minutes'])->toBe('approved')
        ->and($decision->risk_acceptance['accepted_by'])->toBe('quality');
});

test('pilot readiness stays blocked until discovery policy evidence defects and signoffs are complete', function () {
    $actor = phaseThirteenActor(RoleName::NationalOperationsAdministrator);
    $service = app(PhaseThirteenRolloutService::class);
    $assessment = phaseThirteenApprovedAssessment($actor, $service);

    $blocked = $service->reviewPilotReadiness(
        actor: $actor,
        siteAssessment: $assessment,
        policyDecisions: [phaseThirteenApprovedDecision($actor, $service, $assessment, 'identifier_standard')],
        pilotName: 'Unsafe pilot',
        pilotSites: ['Muhimbili'],
        chainCoverage: ['collection'],
        prerequisites: ['hardware_ready'],
        validationEvidence: ['test_suite' => false],
        dataMigrationEvidence: [],
        trainingEvidence: [],
        downtimeRestoreEvidence: [],
        traceabilityRecallEvidence: [],
        openDefects: [['severity' => 'critical', 'summary' => 'wrong-patient issue unresolved']],
        signoffs: ['operations'],
        exitCriteria: ['critical_defects' => 1],
    );

    expect($blocked)->toBeInstanceOf(RolloutPilotReadinessReview::class)
        ->and($blocked->status)->toBe('blocked')
        ->and($blocked->approved_at)->toBeNull()
        ->and($blocked->exit_criteria['blocked_reasons'])->not->toBeEmpty();

    $ready = phaseThirteenReadyPilot($actor, $service, $assessment);

    expect($ready->status)->toBe('ready')
        ->and($ready->approved_by)->toBe($actor->id)
        ->and($ready->chain_coverage)->toContain('transfusion_outcome')
        ->and($ready->traceability_recall_evidence['simulation_passed'])->toBeTrue();
});

test('regional and national scale readiness require ready pilot kpis budget vendor exit and no critical risks', function () {
    $actor = phaseThirteenActor(RoleName::NationalOperationsAdministrator);
    $service = app(PhaseThirteenRolloutService::class);
    $assessment = phaseThirteenApprovedAssessment($actor, $service);
    $blockedPilot = RolloutPilotReadinessReview::factory()->for($assessment, 'siteAssessment')->create(['status' => 'blocked']);

    $blockedScale = $service->reviewScaleReadiness(
        actor: $actor,
        pilotReadinessReview: $blockedPilot,
        scaleLevel: 'regional',
        candidateSites: ['Eastern Zone'],
        readinessCriteria: ['site_assessed'],
        kpiComparison: ['safety' => ['baseline' => 1, 'current' => 1]],
        monitoringPlan: ['weekly_review' => true],
        supportModel: ['helpdesk' => true],
        operatingBudget: [],
        vendorExitPlan: [],
        unresolvedRisks: [['level' => 'critical', 'summary' => 'backup ownership unresolved']],
    );

    expect($blockedScale)->toBeInstanceOf(RolloutScaleReadinessReview::class)
        ->and($blockedScale->status)->toBe('blocked')
        ->and($blockedScale->monitoring_plan['blocked_reasons'])->not->toBeEmpty();

    $readyPilot = phaseThirteenReadyPilot($actor, $service, $assessment);

    $regionalReady = $service->reviewScaleReadiness(
        actor: $actor,
        pilotReadinessReview: $readyPilot,
        scaleLevel: 'regional',
        candidateSites: ['Eastern Zone', 'Lake Zone'],
        readinessCriteria: ['site_assessed', 'users_trained', 'support_ready'],
        kpiComparison: phaseThirteenKpiComparison(),
        monitoringPlan: ['daily_review' => true, 'escalation' => true],
        supportModel: ['helpdesk' => true, 'on_call' => true],
        operatingBudget: [],
        vendorExitPlan: [],
        unresolvedRisks: [['level' => 'medium', 'summary' => 'connectivity monitored']],
    );

    $nationalReady = $service->reviewScaleReadiness(
        actor: $actor,
        pilotReadinessReview: $readyPilot,
        scaleLevel: 'national',
        candidateSites: ['National expansion wave 1'],
        readinessCriteria: ['site_assessed', 'users_trained', 'support_ready', 'budget_approved'],
        kpiComparison: phaseThirteenKpiComparison(),
        monitoringPlan: ['national_command' => true, 'continuous_improvement' => true],
        supportModel: ['helpdesk' => true, 'regional_super_users' => true],
        operatingBudget: phaseThirteenBudgetAreas(),
        vendorExitPlan: phaseThirteenVendorExitAreas(),
        unresolvedRisks: [],
    );

    expect($regionalReady->status)->toBe('ready')
        ->and($regionalReady->approved_at)->not->toBeNull()
        ->and($nationalReady->status)->toBe('ready')
        ->and($nationalReady->operating_budget)->toContain('security')
        ->and($nationalReady->vendor_exit_plan)->toContain('independent_recovery');
});

test('donor cannot manage rollout readiness controls', function () {
    $donor = User::factory()->donor()->create();
    $service = app(PhaseThirteenRolloutService::class);

    expect(fn () => $service->recordSiteAssessment(
        actor: $donor,
        bloodCenter: null,
        siteName: 'Unauthorized',
        siteType: 'blood center',
        workflowMap: phaseThirteenWorkflowMap(),
        inventorySnapshot: phaseThirteenInventorySnapshot(),
        baselineKpis: phaseThirteenBaselineKpis(),
        risks: [['level' => 'low', 'control' => 'review']],
        dataDictionaryScope: ['donor'],
        masterDataOwners: ['donor' => 'operations'],
        safetyCaseReference: 'SAFE-001',
        targetProcessReference: 'PROC-001',
        pilotScope: ['centers' => ['Muhimbili']],
        prioritizedBacklog: ['must' => ['training']],
        legalAndPolicyInputs: ['privacy'],
        operationalReadiness: ['support' => true],
    ))->toThrow(AuthorizationException::class);
});

function phaseThirteenActor(RoleName $role): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([$role->value]);

    return $user;
}

function phaseThirteenApprovedAssessment(User $actor, PhaseThirteenRolloutService $service): RolloutSiteAssessment
{
    return $service->recordSiteAssessment(
        actor: $actor,
        bloodCenter: BloodCenter::factory()->create(),
        siteName: 'Muhimbili controlled pilot',
        siteType: 'blood center',
        workflowMap: phaseThirteenWorkflowMap(),
        inventorySnapshot: phaseThirteenInventorySnapshot(),
        baselineKpis: phaseThirteenBaselineKpis(),
        risks: [
            ['level' => 'medium', 'hazard' => 'connectivity', 'control' => 'downtime procedure'],
            ['level' => 'high', 'hazard' => 'wrong identification', 'control' => 'barcode and identity confirmation'],
        ],
        dataDictionaryScope: ['donor', 'collection', 'laboratory', 'component', 'hospital', 'transfusion'],
        masterDataOwners: ['centers' => 'operations', 'tests' => 'laboratory', 'users' => 'ict'],
        safetyCaseReference: 'SAFE-P13-001',
        targetProcessReference: 'PROC-P13-001',
        pilotScope: ['centers' => ['Muhimbili'], 'hospitals' => ['Pilot hospital'], 'workflows' => phaseThirteenWorkflowMap()],
        prioritizedBacklog: ['critical' => ['identifier approval'], 'must' => ['training'], 'should' => ['dashboard tuning']],
        legalAndPolicyInputs: ['privacy', 'retention', 'clinical_safety', 'hospital_boundary'],
        operationalReadiness: ['training' => true, 'hardware' => true, 'support' => true],
        approve: true,
    );
}

function phaseThirteenApprovedDecision(
    User $actor,
    PhaseThirteenRolloutService $service,
    RolloutSiteAssessment $assessment,
    string $category,
): RolloutPolicyDecision {
    return $service->registerPolicyDecision(
        actor: $actor,
        siteAssessment: $assessment,
        decisionCode: 'P13-'.strtoupper(str_replace('_', '-', $category)),
        category: $category,
        title: str_replace('_', ' ', $category),
        decisionSummary: 'Approved controlled rollout decision for '.$category.'.',
        optionsConsidered: ['legacy control', 'target control'],
        requiredApprovals: ['operations', 'clinical', 'quality'],
        approvalEvidence: ['minutes' => 'approved', 'reference' => 'MIN-P13'],
        riskAcceptance: ['accepted_by' => 'quality', 'residual_risk' => 'controlled'],
        implementationControls: ['change_control', 'training', 'rollback', 'audit'],
        reviewSchedule: ['frequency' => 'monthly', 'owner' => 'quality'],
        status: 'approved',
    );
}

function phaseThirteenReadyPilot(User $actor, PhaseThirteenRolloutService $service, RolloutSiteAssessment $assessment): RolloutPilotReadinessReview
{
    $decisions = array_map(
        fn (string $category): RolloutPolicyDecision => phaseThirteenApprovedDecision($actor, $service, $assessment, $category),
        phaseThirteenPolicyCategories(),
    );

    return $service->reviewPilotReadiness(
        actor: $actor,
        siteAssessment: $assessment,
        policyDecisions: $decisions,
        pilotName: 'Controlled donor-to-recipient pilot',
        pilotSites: ['Muhimbili', 'Pilot hospital'],
        chainCoverage: [
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
        ],
        prerequisites: ['approved_policy', 'hardware_ready', 'sop_deployed', 'support_ready', 'test_environment'],
        validationEvidence: ['test_suite' => true, 'uat' => true, 'browser_device' => true],
        dataMigrationEvidence: ['reconciled' => true, 'exceptions' => 0],
        trainingEvidence: ['competency_records' => true, 'authorized_users' => true],
        downtimeRestoreEvidence: ['restore_test' => true, 'downtime_drill' => true],
        traceabilityRecallEvidence: ['simulation_passed' => true, 'lookback_passed' => true],
        openDefects: [['severity' => 'low', 'summary' => 'training copy polish']],
        signoffs: ['clinical', 'quality', 'operations'],
        exitCriteria: ['critical_defects' => 0, 'data_quality' => 'acceptable'],
    );
}

/** @return list<string> */
function phaseThirteenWorkflowMap(): array
{
    return [
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
}

/** @return array<string, list<string>> */
function phaseThirteenInventorySnapshot(): array
{
    return [
        'analyzers' => ['Analyzer A'],
        'budgets' => ['construction'],
        'centers' => ['Muhimbili'],
        'connectivity' => ['primary'],
        'contracts' => ['support'],
        'equipment' => ['printer'],
        'forms' => ['downtime'],
        'hospitals' => ['Pilot hospital'],
        'identifiers' => ['construction'],
        'integrations' => ['none'],
        'laws' => ['privacy'],
        'power' => ['backup'],
        'reagents' => ['pilot lot'],
        'routes' => ['route a'],
        'sensors' => ['temperature'],
        'sops' => ['collection'],
        'staff' => ['trained cohort'],
        'storage' => ['cold room'],
        'volumes' => ['monthly baseline'],
    ];
}

/** @return array<string, int> */
function phaseThirteenBaselineKpis(): array
{
    return [
        'adoption' => 0,
        'downtime' => 0,
        'expiry' => 0,
        'incident' => 0,
        'request_fill' => 0,
        'safety' => 0,
        'support' => 0,
        'turnaround' => 0,
    ];
}

/** @return list<string> */
function phaseThirteenPolicyCategories(): array
{
    return [
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
}

/** @return array<string, array<string, int>> */
function phaseThirteenKpiComparison(): array
{
    return [
        'adoption' => ['baseline' => 0, 'current' => 1],
        'downtime' => ['baseline' => 0, 'current' => 0],
        'expiry' => ['baseline' => 0, 'current' => 0],
        'incident' => ['baseline' => 0, 'current' => 0],
        'request_fill' => ['baseline' => 0, 'current' => 1],
        'safety' => ['baseline' => 0, 'current' => 1],
        'support' => ['baseline' => 0, 'current' => 1],
        'turnaround' => ['baseline' => 0, 'current' => 1],
    ];
}

/** @return list<string> */
function phaseThirteenBudgetAreas(): array
{
    return [
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
}

/** @return list<string> */
function phaseThirteenVendorExitAreas(): array
{
    return [
        'data_exports',
        'deployment_automation',
        'documentation',
        'handover_drill',
        'independent_recovery',
        'local_administrators',
        'source_ownership',
        'test_suites',
    ];
}
