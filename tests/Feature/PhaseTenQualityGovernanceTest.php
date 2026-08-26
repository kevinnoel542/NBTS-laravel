<?php

use App\EqaAssessmentStatus;
use App\Models\EqaAssessment;
use App\Models\Hospital;
use App\Models\QualityDeviation;
use App\Models\User;
use App\QualityAuditStatus;
use App\QualityDeviationStatus;
use App\QualityDocumentStatus;
use App\QualitySeverity;
use App\QualityTrainingStatus;
use App\RoleName;
use App\Services\QualityManagementService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('critical capa cannot close without root cause action evidence and quality approval', function () {
    $quality = phaseTenQualityActor();
    $deviation = app(QualityManagementService::class)->openDeviation(
        actor: $quality,
        type: 'repeated_screening_nonconformity',
        severity: QualitySeverity::Critical,
        title: 'Repeated screening form mismatch',
        description: 'Three related screening records failed second review in the same week.',
        affectedRecords: [
            ['type' => 'eligibility_record', 'reference' => 'ELG-P10-001'],
        ],
    );

    expect($deviation)->toBeInstanceOf(QualityDeviation::class)
        ->and($deviation->status)->toBe(QualityDeviationStatus::Open)
        ->and($deviation->due_at->isBefore(now()->addDays(3)))->toBeTrue();

    expect(fn () => app(QualityManagementService::class)->closeDeviation(
        deviation: $deviation,
        qualityApprover: $quality,
        rootCause: 'short',
        correctiveAction: 'short',
        preventiveAction: 'short',
        effectivenessCheck: 'short',
        closureEvidence: '',
    ))->toThrow(ValidationException::class);

    $closed = app(QualityManagementService::class)->closeDeviation(
        deviation: $deviation,
        qualityApprover: $quality,
        rootCause: 'Incorrect checklist version remained in circulation after SOP update.',
        correctiveAction: 'Removed obsolete forms and reviewed affected donor records with supervisors.',
        preventiveAction: 'Added controlled print register and daily version check before reception opens.',
        effectivenessCheck: 'Seven-day audit found only current SOP forms in active work areas.',
        closureEvidence: 'CAPA-EVD-P10',
    );

    expect($closed->status)->toBe(QualityDeviationStatus::Closed)
        ->and($closed->quality_approved_by)->toBe($quality->id)
        ->and($closed->closure_evidence)->toBe('CAPA-EVD-P10');
});

test('sop approval links active workflow rules and staff competency records', function () {
    $quality = phaseTenQualityActor();
    $staff = User::factory()->staff()->create();

    $document = app(QualityManagementService::class)->approveDocument(
        approver: $quality,
        code: 'SOP-HV-P10',
        version: 1,
        title: 'Haemovigilance investigation and reporting',
        type: 'sop',
        workflows: ['haemovigilance.donor_reaction', 'haemovigilance.recipient_reaction'],
    );
    $training = app(QualityManagementService::class)->recordTraining(
        staff: $staff,
        verifier: $quality,
        document: $document,
        competencyCode: 'HV-INVESTIGATION-P10',
    );

    expect($document->status)->toBe(QualityDocumentStatus::Effective)
        ->and($document->applies_to_workflows)->toContain('haemovigilance.donor_reaction')
        ->and($document->approved_by)->toBe($quality->id)
        ->and($training->status)->toBe(QualityTrainingStatus::Competent)
        ->and($training->quality_document_id)->toBe($document->id)
        ->and($training->verified_by)->toBe($quality->id)
        ->and($training->valid_until->isFuture())->toBeTrue();
});

test('audit eqa and hospital committee governance records operational evidence', function () {
    $quality = phaseTenQualityActor();
    $lab = User::factory()->staff()->create();
    $lab->syncRoles([RoleName::LaboratoryTechnician->value]);
    $assessment = EqaAssessment::factory()->create();
    $hospital = Hospital::factory()->create();

    $audit = app(QualityManagementService::class)->startAudit(
        auditor: $quality,
        type: 'internal',
        scope: ['workflow' => 'phase_10', 'area' => 'haemovigilance'],
    );
    $eqa = app(QualityManagementService::class)->submitEqa(
        assessment: $assessment,
        actor: $lab,
        submittedResults: ['anti_hiv' => 'reactive'],
        expectedResults: ['anti_hiv' => 'non_reactive'],
    );
    $committee = app(QualityManagementService::class)->recordHospitalCommitteeReview(
        hospital: $hospital,
        chair: $quality,
        metrics: [
            'emergency_release' => ['cases' => 2, 'retrospective_completed' => 2],
            'reactions' => ['febrile' => 1],
            'utilization' => ['rcc_units' => 18],
            'wastage' => ['expired' => 0, 'cold_chain' => 1],
        ],
        actions: ['bedside identification refresher assigned to paediatric ward'],
    );

    expect($audit->status)->toBe(QualityAuditStatus::InProgress)
        ->and($audit->scope['workflow'])->toBe('phase_10')
        ->and($eqa->status)->toBe(EqaAssessmentStatus::Nonconforming)
        ->and($eqa->findings['nonconforming_results'])->toBeTrue()
        ->and($committee->status)->toBe(QualityAuditStatus::Closed)
        ->and($committee->reaction_review['febrile'])->toBe(1)
        ->and($committee->education_actions)->toContain('bedside identification refresher assigned to paediatric ward');
});

test('quality trend snapshot links repeated deviations and audit findings', function () {
    $quality = phaseTenQualityActor();
    $first = QualityDeviation::factory()->create([
        'due_at' => now()->subDay(),
        'severity' => QualitySeverity::Critical,
        'status' => QualityDeviationStatus::Open,
        'type' => 'labelling_error',
    ]);
    $second = QualityDeviation::factory()->create([
        'severity' => QualitySeverity::High,
        'status' => QualityDeviationStatus::CapaInProgress,
        'type' => 'labelling_error',
    ]);
    QualityDeviation::factory()->create([
        'severity' => QualitySeverity::Low,
        'status' => QualityDeviationStatus::Closed,
        'type' => 'documentation_gap',
    ]);
    app(QualityManagementService::class)->startAudit(
        auditor: $quality,
        type: 'internal',
        scope: ['workflow' => 'labelling'],
    )->forceFill([
        'findings' => ['repeat_label_mismatch' => true],
        'linked_deviation_ids' => [$first->id, $second->id],
    ])->save();

    $snapshot = app(QualityManagementService::class)->deviationTrendSnapshot($quality);

    expect($snapshot['repeated_deviation_types'])->toBe(['labelling_error' => 2])
        ->and($snapshot['open_critical_count'])->toBe(1)
        ->and($snapshot['overdue_open_count'])->toBe(1)
        ->and($snapshot['audit_linked_deviation_ids'])->toBe([$first->id, $second->id]);
});

function phaseTenQualityActor(): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::NationalQualityHaemovigilanceOfficer->value]);

    return $user;
}
