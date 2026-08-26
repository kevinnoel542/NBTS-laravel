<?php

namespace App\Services;

use App\Models\AccessReview;
use App\Models\BackupRun;
use App\Models\ChangeControl;
use App\Models\DataProcessingInventory;
use App\Models\IntegrationEndpoint;
use App\Models\IntegrationMessage;
use App\Models\PrivacyNotice;
use App\Models\ProtectedExport;
use App\Models\RecoveryExercise;
use App\Models\RetentionPolicy;
use App\Models\SupportIncident;
use App\Models\User;
use App\PermissionName;
use App\RoleName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhaseElevenGovernanceService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function approveProcessingInventory(User $actor, array $data): DataProcessingInventory
    {
        $this->ensureCan($actor, PermissionName::ManageDataProtection);

        if (($data['dpia_required'] ?? false) && blank($data['dpia_reference'] ?? null)) {
            throw ValidationException::withMessages(['dpia_reference' => ['DPIA reference is required when DPIA is required.']]);
        }

        return DataProcessingInventory::query()->create([
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'breach_response_playbook' => $data['breach_response_playbook'] ?? 'Activate DPO breach triage and regulator notification decision workflow.',
            'controller' => $data['controller'] ?? 'NBTS',
            'data_categories' => $data['data_categories'] ?? ['identity', 'clinical_safety'],
            'data_subjects' => $data['data_subjects'] ?? ['donor', 'recipient'],
            'dpia_reference' => $data['dpia_reference'] ?? null,
            'dpia_required' => (bool) ($data['dpia_required'] ?? true),
            'lawful_basis' => $data['lawful_basis'] ?? 'public_health_task',
            'minimization_controls' => $data['minimization_controls'] ?? ['role_scope', 'hashed_patient_reference'],
            'name' => trim((string) $data['name']),
            'owner_id' => $data['owner_id'] ?? $actor->id,
            'process_code' => $data['process_code'] ?? 'DPI-'.Str::upper(Str::random(8)),
            'processors' => $data['processors'] ?? [],
            'purposes' => $data['purposes'] ?? ['safe_blood_supply'],
            'review_due_at' => $data['review_due_at'] ?? now()->addYear(),
            'rights_handling' => $data['rights_handling'] ?? ['access_request_sla_days' => 30],
            'status' => 'approved',
            'vendor_controls' => $data['vendor_controls'] ?? ['approved_contract', 'security_review'],
        ]);
    }

    /**
     * @param  list<string>  $channels
     * @param  array<string, mixed>  $consentScope
     */
    public function publishPrivacyNotice(User $actor, string $code, int $version, string $title, array $channels, array $consentScope): PrivacyNotice
    {
        $this->ensureCan($actor, PermissionName::ManageDataProtection);

        if ($channels === [] || $consentScope === []) {
            throw ValidationException::withMessages(['notice' => ['Privacy notice requires channels and consent scope.']]);
        }

        return PrivacyNotice::query()->create([
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'channels' => $channels,
            'communication_preferences' => ['opt_out_supported' => true, 'safe_contact_required' => true],
            'consent_scope' => $consentScope,
            'effective_from' => today(),
            'notice_code' => trim($code),
            'status' => 'effective',
            'title' => trim($title),
            'version' => $version,
        ]);
    }

    /**
     * @param  array<string, mixed>  $archiveControls
     */
    public function approveRetentionPolicy(User $actor, string $recordCategory, int $retentionDays, array $archiveControls): RetentionPolicy
    {
        $this->ensureCan($actor, PermissionName::ManageDataProtection);

        if ($retentionDays < 365 || $archiveControls === []) {
            throw ValidationException::withMessages(['retention' => ['Traceability retention requires approved duration and archive controls.']]);
        }

        return RetentionPolicy::query()->updateOrCreate(
            ['record_category' => trim($recordCategory)],
            [
                'approved_at' => now(),
                'approved_by' => $actor->id,
                'archival_after_days' => min(1095, $retentionDays),
                'deletion_restricted' => true,
                'effective_from' => today(),
                'legal_basis' => 'approved_nbts_traceability_retention_policy',
                'retention_period_days' => $retentionDays,
                'secure_archive_controls' => $archiveControls,
                'status' => 'effective',
            ],
        );
    }

    public function assertDeletionAllowed(User $actor, string $recordCategory): void
    {
        $this->ensureCan($actor, PermissionName::ManageDataProtection);

        $policy = RetentionPolicy::query()
            ->where('record_category', trim($recordCategory))
            ->where('status', 'effective')
            ->first();

        if ($policy instanceof RetentionPolicy && $policy->deletion_restricted) {
            throw ValidationException::withMessages(['record' => ['This record category is locked for safety traceability retention.']]);
        }
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $manifest
     */
    public function approveProtectedExport(User $requester, User $approver, string $purpose, string $recipient, array $scope, array $manifest): ProtectedExport
    {
        $this->ensureCan($requester, PermissionName::ExportReports);
        $this->ensureCan($approver, PermissionName::ManageDataProtection);

        if (mb_strlen(trim($purpose)) < 10 || $scope === [] || $manifest === []) {
            throw ValidationException::withMessages(['export' => ['Protected exports require purpose, scope, and encrypted manifest evidence.']]);
        }

        return ProtectedExport::query()->create([
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'delivery_channel' => 'encrypted_download',
            'encrypted_manifest' => $manifest,
            'expires_at' => now()->addDays(7),
            'export_reference' => 'EXP-'.Str::upper(Str::random(10)),
            'purpose' => trim($purpose),
            'recipient' => trim($recipient),
            'requested_by' => $requester->id,
            'scope' => $scope,
            'status' => 'approved',
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function approveIntegrationEndpoint(User $actor, string $systemCode, string $name, string $endpointType, string $standardProfile, string $baseUrl, array $config = []): IntegrationEndpoint
    {
        $this->ensureCan($actor, PermissionName::ManageIntegrations);

        return IntegrationEndpoint::query()->create([
            'acknowledgement_required' => true,
            'approved_at' => now(),
            'base_url' => trim($baseUrl),
            'dead_letter_enabled' => true,
            'encrypted_config' => $config,
            'endpoint_type' => trim($endpointType),
            'idempotency_required' => true,
            'name' => trim($name),
            'owner_id' => $actor->id,
            'retry_policy' => ['max_attempts' => 5, 'backoff_minutes' => [1, 5, 15, 60]],
            'sequence_check_required' => true,
            'standard_profile' => trim($standardProfile),
            'status' => 'approved',
            'system_code' => trim($systemCode),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function receiveIntegrationMessage(IntegrationEndpoint $endpoint, string $idempotencyKey, int $sequenceNumber, string $messageType, array $payload): IntegrationMessage
    {
        if ($endpoint->idempotency_required) {
            $existing = IntegrationMessage::query()
                ->where('integration_endpoint_id', $endpoint->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing instanceof IntegrationMessage) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($endpoint, $idempotencyKey, $sequenceNumber, $messageType, $payload): IntegrationMessage {
            $latestSequence = IntegrationMessage::query()
                ->where('integration_endpoint_id', $endpoint->id)
                ->whereNotNull('sequence_number')
                ->lockForUpdate()
                ->max('sequence_number');
            $sequenceFailed = $endpoint->sequence_check_required
                && is_numeric($latestSequence)
                && $sequenceNumber <= (int) $latestSequence;

            return IntegrationMessage::query()->create([
                'acknowledgement_payload' => $sequenceFailed ? ['accepted' => false, 'reason' => 'sequence_check_failed'] : null,
                'attempts' => 1,
                'dead_lettered_at' => $sequenceFailed ? now() : null,
                'direction' => 'inbound',
                'idempotency_key' => $idempotencyKey,
                'integration_endpoint_id' => $endpoint->id,
                'last_error' => $sequenceFailed ? 'Message sequence is not newer than the last accepted message.' : null,
                'message_reference' => 'MSG-'.Str::upper(Str::random(10)),
                'message_type' => trim($messageType),
                'next_retry_at' => $sequenceFailed ? null : now()->addMinute(),
                'payload_digest' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
                'sequence_number' => $sequenceNumber,
                'status' => $sequenceFailed ? 'dead_lettered' : 'received',
            ]);
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $acknowledgement
     */
    public function acknowledgeIntegrationMessage(IntegrationMessage $message, array $acknowledgement): IntegrationMessage
    {
        $message->forceFill([
            'acknowledged_at' => now(),
            'acknowledgement_payload' => $acknowledgement,
            'last_error' => null,
            'next_retry_at' => null,
            'reconciled_at' => now(),
            'status' => 'acknowledged',
        ])->save();

        return $message->refresh();
    }

    public function enforceHighRiskMfa(User $user): void
    {
        if (! $this->isHighRiskUser($user)) {
            return;
        }

        if ($user->two_factor_confirmed_at === null) {
            throw ValidationException::withMessages(['mfa' => ['High-risk accounts must confirm MFA before privileged work.']]);
        }
    }

    public function openAccessReview(User $actor): AccessReview
    {
        $this->ensureCan($actor, PermissionName::ManageSecurityOperations);

        return AccessReview::query()->create([
            'conflicts' => $this->separationConflictReport(),
            'due_at' => now()->addMonth(),
            'findings' => ['least_privilege_review_required' => true],
            'high_risk_roles' => $this->highRiskRoleValues(),
            'owner_id' => $actor->id,
            'review_reference' => 'AR-'.Str::upper(Str::random(10)),
            'scope' => ['privileged_roles', 'clinical_roles', 'export_access', 'assignment_conflicts'],
            'status' => 'open',
        ]);
    }

    public function recordBackupRun(User $actor, string $backupType, string $storageLocation, int $sizeBytes, ?string $checksum, bool $encrypted, bool $offsite): BackupRun
    {
        $this->ensureCan($actor, PermissionName::ManageBackups);

        if (! $encrypted || ! $offsite || $sizeBytes <= 0 || blank($checksum)) {
            throw ValidationException::withMessages(['backup' => ['Successful backups require encryption, off-site storage, size, and checksum evidence.']]);
        }

        return BackupRun::query()->create([
            'backup_reference' => 'BKP-'.Str::upper(Str::random(10)),
            'backup_type' => trim($backupType),
            'checksum' => trim((string) $checksum),
            'completed_at' => now(),
            'encrypted' => true,
            'offsite' => true,
            'operator_id' => $actor->id,
            'retention_until' => now()->addYear(),
            'size_bytes' => $sizeBytes,
            'started_at' => now()->subMinutes(10),
            'status' => 'verified',
            'storage_location' => trim($storageLocation),
            'verified_at' => now(),
        ]);
    }

    /**
     * @param  array<string, bool>  $validationChecks
     * @param  list<string>  $exceptions
     */
    public function recordRecoveryExercise(User $operator, User $approver, string $scenario, int $rtoMinutes, int $rpoMinutes, array $validationChecks, array $exceptions = []): RecoveryExercise
    {
        $this->ensureCan($operator, PermissionName::ManageBackups);

        foreach (['identification_controls', 'quarantine_controls', 'release_controls', 'traceability_controls'] as $requiredCheck) {
            if (($validationChecks[$requiredCheck] ?? false) !== true) {
                throw ValidationException::withMessages(['validation_checks' => ['Recovery exercise must validate identification, quarantine, release, and traceability controls.']]);
            }
        }

        return RecoveryExercise::query()->create([
            'approver_id' => $approver->id,
            'capa_reference' => $exceptions === [] ? null : 'CAPA-'.Str::upper(Str::random(8)),
            'exceptions' => $exceptions,
            'exercise_reference' => 'REC-'.Str::upper(Str::random(10)),
            'operator_id' => $operator->id,
            'recovered_at' => now(),
            'recovery_point_at' => now()->subMinutes($rpoMinutes),
            'reopening_approved_at' => now(),
            'rpo_minutes' => $rpoMinutes,
            'rto_minutes' => $rtoMinutes,
            'scenario' => trim($scenario),
            'status' => $exceptions === [] ? 'passed' : 'passed_with_exceptions',
            'validation_checks' => $validationChecks,
        ]);
    }

    /**
     * @param  list<string>  $escalationTargets
     */
    public function openIncident(User $actor, string $severity, string $service, string $impact, array $escalationTargets): SupportIncident
    {
        if (! $actor->can(PermissionName::ManageIncidents->value) && ! $actor->can(PermissionName::ManageSecurityOperations->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot open support incidents.']]);
        }

        if (in_array($severity, ['critical', 'high'], true) && $escalationTargets === []) {
            throw ValidationException::withMessages(['escalation_targets' => ['High and critical incidents require escalation targets.']]);
        }

        return SupportIncident::query()->create([
            'acknowledged_at' => now(),
            'communication_log' => [['at' => now()->toIso8601String(), 'message' => 'Incident opened and owner notified.']],
            'escalation_targets' => $escalationTargets,
            'impact' => trim($impact),
            'incident_reference' => 'INC-'.Str::upper(Str::random(10)),
            'owner_id' => $actor->id,
            'service' => trim($service),
            'severity' => trim($severity),
            'status' => 'open',
            'workaround' => 'Operational workaround required if service remains degraded beyond SLA.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, bool>  $approvals
     * @param  array<string, mixed>  $regressionEvidence
     */
    public function approveChange(User $requester, User $approver, string $classification, string $title, array $scope, array $approvals, array $regressionEvidence, bool $emergencyChange = false): ChangeControl
    {
        $this->ensureCan($approver, PermissionName::ManageChangeControls);

        $requiredApprovals = match ($classification) {
            'clinical_safety' => ['clinical', 'laboratory', 'quality', 'validation'],
            'privacy_data' => ['dpo', 'legal', 'data_governance'],
            'infrastructure' => ['ict_security', 'operations'],
            default => ['operations'],
        };

        foreach ($requiredApprovals as $requiredApproval) {
            if (($approvals[$requiredApproval] ?? false) !== true) {
                throw ValidationException::withMessages(['approvals' => ['This change is missing required '.$requiredApproval.' approval.']]);
            }
        }

        if ($scope === [] || $regressionEvidence === []) {
            throw ValidationException::withMessages(['change' => ['Change control requires scope and regression evidence.']]);
        }

        return ChangeControl::query()->create([
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'approvals' => $approvals,
            'change_reference' => 'CHG-'.Str::upper(Str::random(10)),
            'classification' => trim($classification),
            'effective_at' => $emergencyChange ? now() : now()->addDay(),
            'emergency_change' => $emergencyChange,
            'migration_plan' => 'Apply through controlled deployment after validation evidence is attached.',
            'regression_evidence' => $regressionEvidence,
            'release_notes' => 'Approved controlled change with owner evidence.',
            'requested_by' => $requester->id,
            'retrospective_review_due_at' => $emergencyChange ? now()->addDays(2) : null,
            'risk_level' => in_array($classification, ['clinical_safety', 'privacy_data'], true) ? 'high' : 'medium',
            'rollback_plan' => 'Restore previous approved configuration and notify affected services.',
            'scope' => $scope,
            'status' => 'approved',
            'title' => trim($title),
            'training_impact' => 'Owner must review training impact before effective date.',
        ]);
    }

    private function ensureCan(User $actor, PermissionName $permission): void
    {
        if (! $actor->can($permission->value)) {
            throw ValidationException::withMessages(['actor' => ['This account is not authorized for '.$permission->value.'.']]);
        }
    }

    private function isHighRiskUser(User $user): bool
    {
        return $user->hasAnyRole($this->highRiskRoleValues())
            || $user->can(PermissionName::ApproveLaboratoryRelease->value)
            || $user->can(PermissionName::ManageRecalls->value)
            || $user->can(PermissionName::ManageBackups->value)
            || $user->can(PermissionName::ManageSettings->value)
            || $user->can(PermissionName::ManageChangeControls->value);
    }

    /** @return list<string> */
    private function highRiskRoleValues(): array
    {
        return [
            RoleName::SuperAdmin->value,
            RoleName::NbtsAdmin->value,
            RoleName::IctSecurityOperator->value,
            RoleName::NationalOperationsAdministrator->value,
            RoleName::NationalQualityHaemovigilanceOfficer->value,
            RoleName::DataProtectionGovernanceOfficer->value,
            RoleName::LaboratoryApproverQualityOfficer->value,
            RoleName::CenterHaemovigilanceQualityOfficer->value,
            RoleName::HospitalHaemovigilanceOfficer->value,
        ];
    }

    /** @return list<array{user_id:int, roles:list<string>}> */
    private function separationConflictReport(): array
    {
        /** @var Collection<int, User> $users */
        $users = User::query()->with('roles:id,name')->get();
        $highRiskRoles = $this->highRiskRoleValues();

        return $users
            ->map(function (User $user) use ($highRiskRoles): ?array {
                $roles = $user->roles
                    ->pluck('name')
                    ->filter(fn (string $role): bool => in_array($role, $highRiskRoles, true))
                    ->values()
                    ->all();

                return count($roles) > 1
                    ? ['roles' => $roles, 'user_id' => $user->id]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
