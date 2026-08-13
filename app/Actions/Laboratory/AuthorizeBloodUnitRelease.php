<?php

namespace App\Actions\Laboratory;

use App\BloodUnitStatus;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\InventoryAdjustment;
use App\Models\ReleaseAuthorization;
use App\Models\User;
use App\ReleaseAuthorizationDecision;
use App\Services\BloodUnitQuarantineService;
use App\Services\InventoryStockAlertService;
use App\Services\ReleaseAuthorizationEvaluator;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class AuthorizeBloodUnitRelease
{
    public function __construct(
        private AuditLogger $auditLogger,
        private BloodUnitQuarantineService $bloodUnitQuarantineService,
        private ReleaseAuthorizationEvaluator $evaluator,
        private InventoryStockAlertService $inventoryStockAlertService,
    ) {}

    /**
     * @param  list<string>  $exceptions
     */
    public function execute(
        BloodUnit $bloodUnit,
        User $approver,
        string $reason,
        bool $electronicSignature,
        ?User $independentApprover = null,
        bool $emergencyOverride = false,
        array $exceptions = [],
    ): ReleaseAuthorization {
        if (! $electronicSignature) {
            throw ValidationException::withMessages([
                'electronic_signature' => ['An electronic signature marker is required before a release decision can be recorded.'],
            ]);
        }

        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages([
                'reason' => ['A clear release reason is required.'],
            ]);
        }

        return DB::transaction(function () use (
            $bloodUnit,
            $approver,
            $reason,
            $electronicSignature,
            $independentApprover,
            $emergencyOverride,
            $exceptions,
        ): ReleaseAuthorization {
            $lockedUnit = BloodUnit::query()
                ->with(['bloodCenter', 'quarantine'])
                ->lockForUpdate()
                ->whereKey($bloodUnit->getKey())
                ->firstOrFail();

            Gate::forUser($approver)->authorize('authorizeRelease', $lockedUnit);

            $evaluation = $this->evaluator->evaluate($lockedUnit);
            $combinedExceptions = array_values(array_unique([...$evaluation['exceptions'], ...$exceptions]));

            foreach ($this->bloodUnitQuarantineService->unresolvedBlockingReasons($lockedUnit) as $quarantineReason) {
                $combinedExceptions[] = "unresolved_quarantine:{$quarantineReason}";
            }

            $combinedExceptions = array_values(array_unique($combinedExceptions));

            if (! $emergencyOverride && in_array($approver->id, $evaluation['test_actor_ids'], true)) {
                $combinedExceptions[] = 'releaser_participated_in_testing';
            }

            if ($emergencyOverride || $exceptions !== []) {
                $this->assertIndependentApproval($lockedUnit, $approver, $independentApprover, $evaluation['test_actor_ids']);
            }

            $decision = $this->decision($evaluation['eligible'], $emergencyOverride, $combinedExceptions);
            $releasedBy = null;

            if ($decision === ReleaseAuthorizationDecision::RoutineRelease) {
                $this->bloodUnitQuarantineService->completeReleaseCriteria($lockedUnit, $approver);
                $this->releaseToAvailableInventory($lockedUnit, $approver, trim($reason));
                $releasedBy = $approver->id;
            }

            $authorization = ReleaseAuthorization::query()->create([
                'approved_by' => $approver->id,
                'authorized_at' => now(),
                'blood_unit_id' => $lockedUnit->id,
                'criteria_version' => $evaluation['criteria_version'],
                'decision' => $decision,
                'electronic_signature' => $electronicSignature,
                'evaluated_tests' => $evaluation['evaluated_tests'],
                'exceptions' => $combinedExceptions,
                'independent_approved_by' => $independentApprover?->id,
                'reason' => trim($reason),
                'released_by' => $releasedBy,
            ]);

            $this->auditLogger->record(
                actor: $approver,
                action: 'laboratory.release_decision_recorded',
                subject: $authorization,
                bloodCenter: $lockedUnit->bloodCenter,
                metadata: [
                    'blood_unit_id' => $lockedUnit->id,
                    'criteria_version' => $evaluation['criteria_version'],
                    'decision' => $decision->value,
                    'emergency_override' => $emergencyOverride,
                    'exceptions' => $combinedExceptions,
                    'independent_approved_by' => $independentApprover?->id,
                    'released_by' => $releasedBy,
                ],
            );

            return $authorization->refresh()->load(['bloodUnit', 'approver', 'independentApprover', 'releaser']);
        }, attempts: 3);
    }

    /**
     * @param  list<int>  $testActorIds
     */
    private function assertIndependentApproval(BloodUnit $bloodUnit, User $approver, ?User $independentApprover, array $testActorIds): void
    {
        if (! $independentApprover instanceof User) {
            throw ValidationException::withMessages([
                'independent_approved_by' => ['An independent approver is required for exception or emergency release decisions.'],
            ]);
        }

        Gate::forUser($independentApprover)->authorize('authorizeRelease', $bloodUnit);

        if ($independentApprover->id === $approver->id || in_array($independentApprover->id, $testActorIds, true)) {
            throw ValidationException::withMessages([
                'independent_approved_by' => ['The independent approver must be separate from the approver, tester, and verifier.'],
            ]);
        }
    }

    /**
     * @param  list<string>  $exceptions
     */
    private function decision(bool $eligible, bool $emergencyOverride, array $exceptions): ReleaseAuthorizationDecision
    {
        if ($emergencyOverride) {
            return ReleaseAuthorizationDecision::EmergencyOverride;
        }

        return $eligible && $exceptions === []
            ? ReleaseAuthorizationDecision::RoutineRelease
            : ReleaseAuthorizationDecision::Rejected;
    }

    private function releaseToAvailableInventory(BloodUnit $bloodUnit, User $approver, string $reason): void
    {
        $bloodUnit->refresh()->load('quarantine');

        if (! $bloodUnit->status->canTransitionTo(BloodUnitStatus::Available)) {
            throw ValidationException::withMessages([
                'blood_unit_id' => ['The blood unit is not in a releasable workflow status.'],
            ]);
        }

        $this->bloodUnitQuarantineService->assertCanMoveToAvailable($bloodUnit);

        BloodInventory::query()->firstOrCreate(
            [
                'blood_center_id' => $bloodUnit->blood_center_id,
                'blood_group' => $bloodUnit->blood_group,
            ],
            [
                'available_units' => 0,
                'minimum_threshold' => 5,
                'reserved_units' => 0,
            ],
        );

        $inventory = BloodInventory::query()
            ->lockForUpdate()
            ->where('blood_center_id', $bloodUnit->blood_center_id)
            ->where('blood_group', $bloodUnit->blood_group)
            ->firstOrFail();

        $previousStatus = $bloodUnit->status;
        $bloodUnit->forceFill([
            'current_location' => 'Available stock',
            'handled_by' => $approver->id,
            'status' => BloodUnitStatus::Available,
        ])->save();

        $inventory->forceFill([
            'available_units' => $inventory->available_units + 1,
        ])->save();

        InventoryAdjustment::query()->create([
            'adjusted_by' => $approver->id,
            'blood_center_id' => $bloodUnit->blood_center_id,
            'blood_group' => $bloodUnit->blood_group,
            'blood_unit_id' => $bloodUnit->id,
            'notes' => $reason,
            'quantity_delta' => 1,
            'reason' => 'laboratory_release_authorized',
            'reserved_quantity_delta' => 0,
        ]);

        $this->inventoryStockAlertService->evaluate($inventory->refresh());

        $this->auditLogger->record(
            actor: $approver,
            action: 'blood_units.laboratory_released',
            subject: $bloodUnit,
            bloodCenter: $bloodUnit->bloodCenter,
            metadata: [
                'available_quantity_delta' => 1,
                'from_status' => $previousStatus->value,
                'to_status' => BloodUnitStatus::Available->value,
            ],
        );
    }
}
