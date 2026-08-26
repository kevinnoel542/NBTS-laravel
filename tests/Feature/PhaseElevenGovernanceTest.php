<?php

use App\Models\ChangeControl;
use App\Models\DataProcessingInventory;
use App\Models\IntegrationMessage;
use App\Models\ProtectedExport;
use App\Models\RetentionPolicy;
use App\Models\User;
use App\RoleName;
use App\Services\PhaseElevenGovernanceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('privacy inventory notices retention and protected exports require DPO controls', function () {
    $dpo = phaseElevenActor(RoleName::DataProtectionGovernanceOfficer);
    $exportRequester = phaseElevenActor(RoleName::SuperAdmin);
    $service = app(PhaseElevenGovernanceService::class);

    expect(fn () => $service->approveProcessingInventory($dpo, [
        'name' => 'Recipient traceability processing',
        'dpia_required' => true,
    ]))->toThrow(ValidationException::class);

    $inventory = $service->approveProcessingInventory($dpo, [
        'name' => 'Recipient traceability processing',
        'dpia_required' => true,
        'dpia_reference' => 'DPIA-P11-001',
        'data_subjects' => ['recipient'],
        'data_categories' => ['patient_reference_hash', 'transfusion_outcome'],
        'purposes' => ['recipient_safety_traceability'],
    ]);
    $notice = $service->publishPrivacyNotice(
        actor: $dpo,
        code: 'PN-P11',
        version: 1,
        title: 'National blood service privacy notice',
        channels: ['web', 'mobile', 'assisted_registration'],
        consentScope: ['appointment_reminders' => true, 'safe_opt_out' => true],
    );
    $retention = $service->approveRetentionPolicy(
        actor: $dpo,
        recordCategory: 'recipient_traceability',
        retentionDays: 3650,
        archiveControls: ['encrypted_archive' => true, 'access_review' => true],
    );

    expect($inventory)->toBeInstanceOf(DataProcessingInventory::class)
        ->and($inventory->status)->toBe('approved')
        ->and($inventory->dpia_reference)->toBe('DPIA-P11-001')
        ->and($notice->status)->toBe('effective')
        ->and($notice->communication_preferences['opt_out_supported'])->toBeTrue()
        ->and($retention)->toBeInstanceOf(RetentionPolicy::class)
        ->and($retention->deletion_restricted)->toBeTrue()
        ->and(fn () => $service->assertDeletionAllowed($dpo, 'recipient_traceability'))->toThrow(ValidationException::class);

    $export = $service->approveProtectedExport(
        requester: $exportRequester,
        approver: $dpo,
        purpose: 'Regulator approved haemovigilance safety review',
        recipient: 'National public health authority',
        scope: ['fields' => ['reaction_type', 'component_identifier'], 'period' => '2026-Q3'],
        manifest: ['checksum' => str_repeat('a', 64), 'format' => 'csv'],
    );

    expect($export)->toBeInstanceOf(ProtectedExport::class)
        ->and($export->status)->toBe('approved')
        ->and($export->encrypted_manifest['format'])->toBe('csv')
        ->and($export->getRawOriginal('encrypted_manifest'))->not->toContain('csv');
});

test('integration endpoint enforces idempotency sequence checks acknowledgements and dead letters', function () {
    $operator = phaseElevenActor(RoleName::IctSecurityOperator);
    $service = app(PhaseElevenGovernanceService::class);
    $endpoint = $service->approveIntegrationEndpoint(
        actor: $operator,
        systemCode: 'HMIS-P11',
        name: 'Approved HMIS gateway',
        endpointType: 'hmis',
        standardProfile: 'fhir-r4-ministry-profile',
        baseUrl: 'https://hmis.example.test/fhir',
        config: ['token' => 'secret-token'],
    );

    $received = $service->receiveIntegrationMessage($endpoint, 'idem-p11-1', 1, 'Observation', ['result' => 'accepted']);
    $acknowledged = $service->acknowledgeIntegrationMessage($received, ['accepted' => true]);
    $duplicate = $service->receiveIntegrationMessage($endpoint, 'idem-p11-1', 1, 'Observation', ['result' => 'accepted']);
    $deadLetter = $service->receiveIntegrationMessage($endpoint, 'idem-p11-2', 1, 'Observation', ['result' => 'late_duplicate']);

    expect($endpoint->acknowledgement_required)->toBeTrue()
        ->and($endpoint->idempotency_required)->toBeTrue()
        ->and($endpoint->sequence_check_required)->toBeTrue()
        ->and($endpoint->dead_letter_enabled)->toBeTrue()
        ->and($endpoint->encrypted_config['token'])->toBe('secret-token')
        ->and($endpoint->getRawOriginal('encrypted_config'))->not->toContain('secret-token')
        ->and($acknowledged->status)->toBe('acknowledged')
        ->and($acknowledged->acknowledged_at)->not->toBeNull()
        ->and($duplicate->id)->toBe($received->id)
        ->and($deadLetter)->toBeInstanceOf(IntegrationMessage::class)
        ->and($deadLetter->status)->toBe('dead_lettered')
        ->and($deadLetter->last_error)->toContain('sequence')
        ->and($deadLetter->dead_lettered_at)->not->toBeNull();
});

test('high risk access requires mfa and access review exposes separation conflicts', function () {
    $security = phaseElevenActor(RoleName::IctSecurityOperator);
    $conflicted = phaseElevenActor(RoleName::SuperAdmin);
    $conflicted->assignRole(RoleName::NationalQualityHaemovigilanceOfficer->value);
    $labApprover = phaseElevenActor(RoleName::LaboratoryApproverQualityOfficer);
    $service = app(PhaseElevenGovernanceService::class);

    expect(fn () => $service->enforceHighRiskMfa($labApprover))->toThrow(ValidationException::class);

    $labApprover->forceFill(['two_factor_confirmed_at' => now()])->save();
    $service->enforceHighRiskMfa($labApprover->fresh());

    $review = $service->openAccessReview($security);

    expect($review->status)->toBe('open')
        ->and($review->high_risk_roles)->toContain(RoleName::SuperAdmin->value)
        ->and(collect($review->conflicts)->contains(fn (array $conflict): bool => $conflict['user_id'] === $conflicted->id))->toBeTrue();
});

test('backup recovery incidents and change control preserve safety evidence', function () {
    $ict = phaseElevenActor(RoleName::IctSecurityOperator);
    $quality = phaseElevenActor(RoleName::NationalQualityHaemovigilanceOfficer);
    $requester = phaseElevenActor(RoleName::NationalOperationsAdministrator);
    $service = app(PhaseElevenGovernanceService::class);

    expect(fn () => $service->recordBackupRun($ict, 'database', 'offsite-vault', 0, null, true, true))
        ->toThrow(ValidationException::class);

    $backup = $service->recordBackupRun(
        actor: $ict,
        backupType: 'database',
        storageLocation: 'offsite-vault',
        sizeBytes: 2048,
        checksum: str_repeat('b', 64),
        encrypted: true,
        offsite: true,
    );

    expect(fn () => $service->recordRecoveryExercise($ict, $ict, 'database_restore', 240, 60, [
        'identification_controls' => true,
    ]))->toThrow(ValidationException::class);

    $recovery = $service->recordRecoveryExercise($ict, $ict, 'database_restore', 240, 60, [
        'identification_controls' => true,
        'quarantine_controls' => true,
        'release_controls' => true,
        'traceability_controls' => true,
    ]);

    expect(fn () => $service->openIncident($ict, 'critical', 'blood_issue', 'Unsafe release risk detected.', []))
        ->toThrow(ValidationException::class);

    $incident = $service->openIncident(
        actor: $ict,
        severity: 'critical',
        service: 'blood_issue',
        impact: 'Unsafe release risk detected and issue workflow paused.',
        escalationTargets: ['ict_security', 'quality', 'operations'],
    );

    expect(fn () => $service->approveChange(
        requester: $requester,
        approver: $quality,
        classification: 'clinical_safety',
        title: 'Compatibility rule update',
        scope: ['workflow' => 'compatibility'],
        approvals: ['clinical' => true],
        regressionEvidence: ['tests' => ['PhaseNineCompatibilityIssueTest']],
    ))->toThrow(ValidationException::class);

    $change = $service->approveChange(
        requester: $requester,
        approver: $quality,
        classification: 'clinical_safety',
        title: 'Compatibility rule update',
        scope: ['workflow' => 'compatibility'],
        approvals: ['clinical' => true, 'laboratory' => true, 'quality' => true, 'validation' => true],
        regressionEvidence: ['tests' => ['PhaseNineCompatibilityIssueTest']],
    );

    expect($backup->status)->toBe('verified')
        ->and($backup->verified_at)->not->toBeNull()
        ->and($recovery->status)->toBe('passed')
        ->and($recovery->validation_checks['traceability_controls'])->toBeTrue()
        ->and($incident->severity)->toBe('critical')
        ->and($incident->escalation_targets)->toContain('quality')
        ->and($change)->toBeInstanceOf(ChangeControl::class)
        ->and($change->status)->toBe('approved')
        ->and($change->approvals['validation'])->toBeTrue();
});

function phaseElevenActor(RoleName $role): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([$role->value]);

    return $user;
}
