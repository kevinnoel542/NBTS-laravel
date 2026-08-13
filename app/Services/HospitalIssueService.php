<?php

namespace App\Services;

use App\CompatibilityResult;
use App\CompatibilityTestStatus;
use App\ComponentStatus;
use App\EmergencyReleaseStatus;
use App\HospitalAllocationStatus;
use App\HospitalReceiptStatus;
use App\HospitalRequestStatus;
use App\Models\BloodComponent;
use App\Models\CompatibilityTest;
use App\Models\EmergencyReleaseAuthorization;
use App\Models\Hospital;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalComponentAllocation;
use App\Models\HospitalComponentReceipt;
use App\Models\PatientSpecimen;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\PermissionName;
use App\TransfusionRecordStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class HospitalIssueService
{
    public function __construct(private HospitalAccessService $hospitalAccess) {}

    public function allocateFefo(HospitalBloodRequest $request, User $actor): HospitalComponentAllocation
    {
        if (! $actor->can(PermissionName::ManageBloodIssue->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot allocate hospital components.']]);
        }

        $request->loadMissing('hospital');
        $this->hospitalAccess->ensure($actor, $request->hospital);

        return DB::transaction(function () use ($request, $actor): HospitalComponentAllocation {
            $lockedRequest = HospitalBloodRequest::query()->lockForUpdate()->findOrFail($request->id);

            if (in_array($lockedRequest->status, [HospitalRequestStatus::Cancelled, HospitalRequestStatus::Fulfilled], true)) {
                throw ValidationException::withMessages(['request' => ['This request is not open for allocation.']]);
            }

            $compatibilityTest = $this->nextCompatibleTest($lockedRequest);
            $emergencyRelease = null;

            if (! $compatibilityTest instanceof CompatibilityTest) {
                $emergencyRelease = $this->nextEmergencyRelease($lockedRequest);
            }

            if (! $compatibilityTest instanceof CompatibilityTest && ! $emergencyRelease instanceof EmergencyReleaseAuthorization) {
                throw ValidationException::withMessages(['compatibility' => ['Allocation requires compatible testing or valid emergency release authorization.']]);
            }

            $componentId = $compatibilityTest instanceof CompatibilityTest
                ? $compatibilityTest->blood_component_id
                : $emergencyRelease->blood_component_id;
            $component = BloodComponent::query()->lockForUpdate()->findOrFail($componentId);

            if (! $component->isAvailableForAllocation()) {
                throw ValidationException::withMessages(['component' => ['Selected component is not available for allocation.']]);
            }

            $component->forceFill([
                'allocated_at' => now(),
                'status' => ComponentStatus::Allocated,
            ])->save();

            $allocation = HospitalComponentAllocation::query()->create([
                'allocated_at' => now(),
                'allocated_by' => $actor->id,
                'blood_component_id' => $component->id,
                'compatibility_test_id' => $compatibilityTest?->id,
                'emergency_release_authorization_id' => $emergencyRelease?->id,
                'expires_at' => now()->addHours(6),
                'hospital_blood_request_id' => $lockedRequest->id,
                'issue_reference' => 'ISS-'.Str::upper(Str::random(10)),
                'status' => HospitalAllocationStatus::Allocated,
            ]);

            $lockedRequest->forceFill([
                'quantity_allocated' => $lockedRequest->quantity_allocated + 1,
                'status' => ($lockedRequest->quantity_allocated + 1) >= $lockedRequest->quantity_requested
                    ? HospitalRequestStatus::Fulfilled
                    : HospitalRequestStatus::PartiallyFilled,
                'fulfilled_at' => ($lockedRequest->quantity_allocated + 1) >= $lockedRequest->quantity_requested ? now() : null,
                'partially_filled_at' => ($lockedRequest->quantity_allocated + 1) < $lockedRequest->quantity_requested ? now() : $lockedRequest->partially_filled_at,
            ])->save();

            return $allocation->fresh(['component', 'bloodRequest']);
        }, attempts: 3);
    }

    /**
     * @param  array<string, bool>  $finalCheck
     */
    public function issue(HospitalComponentAllocation $allocation, User $actor, array $finalCheck): HospitalComponentAllocation
    {
        if (! $actor->can(PermissionName::ManageBloodIssue->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot issue hospital components.']]);
        }

        $allocation->loadMissing('bloodRequest.hospital');
        $this->hospitalAccess->ensure($actor, $allocation->bloodRequest->hospital);
        $this->assertFinalIssueCheck($finalCheck);

        return DB::transaction(function () use ($allocation, $actor, $finalCheck): HospitalComponentAllocation {
            $record = HospitalComponentAllocation::query()->with('component')->lockForUpdate()->findOrFail($allocation->id);

            if ($record->status !== HospitalAllocationStatus::Allocated || $record->component->status !== ComponentStatus::Allocated) {
                throw ValidationException::withMessages(['allocation' => ['Only allocated components can be issued.']]);
            }

            $record->component->forceFill([
                'issued_at' => now(),
                'status' => ComponentStatus::Issued,
            ])->save();

            $record->forceFill([
                'final_check' => $finalCheck,
                'issue_checked_by' => $actor->id,
                'issued_at' => now(),
                'status' => HospitalAllocationStatus::Issued,
            ])->save();

            return $record->refresh()->load('component', 'bloodRequest');
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $temperatureEvidence
     * @param  array<int, string>  $chainOfCustody
     */
    public function recordReceipt(
        HospitalComponentAllocation $allocation,
        User $actor,
        string $condition,
        array $temperatureEvidence,
        array $chainOfCustody,
        ?string $discrepancyNotes = null,
    ): HospitalComponentReceipt {
        if (! $actor->can(PermissionName::ManageBloodIssue->value) && ! $actor->can(PermissionName::RecordTransfusions->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot record hospital receipt.']]);
        }

        $allocation->loadMissing('bloodRequest.hospital');
        $this->hospitalAccess->ensure($actor, $allocation->bloodRequest->hospital);

        return DB::transaction(function () use ($allocation, $actor, $condition, $temperatureEvidence, $chainOfCustody, $discrepancyNotes): HospitalComponentReceipt {
            $record = HospitalComponentAllocation::query()->with('component', 'bloodRequest')->lockForUpdate()->findOrFail($allocation->id);

            if ($record->status !== HospitalAllocationStatus::Issued || $record->component->status !== ComponentStatus::Issued) {
                throw ValidationException::withMessages(['allocation' => ['Only issued components can be received by the hospital.']]);
            }

            $accepted = mb_strtolower(trim($condition)) === 'intact'
                && $temperatureEvidence !== []
                && $chainOfCustody !== [];

            if (! $accepted) {
                $record->component->forceFill([
                    'investigation_hold_at' => now(),
                    'status' => ComponentStatus::InvestigationHold,
                ])->save();
            }

            $record->forceFill([
                'status' => $accepted ? HospitalAllocationStatus::Received : HospitalAllocationStatus::Returned,
            ])->save();

            return HospitalComponentReceipt::query()->create([
                'blood_component_id' => $record->blood_component_id,
                'chain_of_custody' => $chainOfCustody,
                'condition' => trim($condition),
                'discrepancy_notes' => $discrepancyNotes,
                'hospital_blood_request_id' => $record->hospital_blood_request_id,
                'hospital_component_allocation_id' => $record->id,
                'hospital_id' => $record->bloodRequest->hospital_id,
                'received_at' => now(),
                'received_by' => $actor->id,
                'status' => $accepted ? HospitalReceiptStatus::Accepted : HospitalReceiptStatus::Hold,
                'temperature_evidence' => $temperatureEvidence,
            ]);
        }, attempts: 3);
    }

    /**
     * @param  array<string, bool>  $bedsideChecks
     * @param  array<string, mixed>  $observations
     */
    public function recordTransfusion(
        HospitalComponentAllocation $allocation,
        PatientSpecimen $specimen,
        HospitalComponentReceipt $receipt,
        User $nurse,
        array $bedsideChecks,
        ?int $volumeMl,
        string $outcome,
        ?string $unusedComponentDisposition = null,
        array $observations = [],
    ): TransfusionRecord {
        if (! $nurse->can(PermissionName::RecordTransfusions->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot record transfusions.']]);
        }

        $allocation->loadMissing('bloodRequest.hospital');
        $this->hospitalAccess->ensure($nurse, $allocation->bloodRequest->hospital);
        $this->assertBedsideChecks($bedsideChecks);

        return DB::transaction(function () use ($allocation, $specimen, $receipt, $nurse, $bedsideChecks, $volumeMl, $outcome, $unusedComponentDisposition, $observations): TransfusionRecord {
            $record = HospitalComponentAllocation::query()->with('component')->lockForUpdate()->findOrFail($allocation->id);
            $lockedSpecimen = PatientSpecimen::query()->lockForUpdate()->findOrFail($specimen->id);
            $lockedReceipt = HospitalComponentReceipt::query()->lockForUpdate()->findOrFail($receipt->id);

            if ($record->status !== HospitalAllocationStatus::Received
                || $lockedReceipt->status !== HospitalReceiptStatus::Accepted
                || $record->hospital_blood_request_id !== $lockedSpecimen->hospital_blood_request_id
                || $record->hospital_blood_request_id !== $lockedReceipt->hospital_blood_request_id
                || $record->blood_component_id !== $lockedReceipt->blood_component_id
                || $record->component->status !== ComponentStatus::Issued) {
                throw ValidationException::withMessages(['transfusion' => ['Transfusion requires the right request, specimen, component, receipt, and issued state.']]);
            }

            if (TransfusionRecord::query()->where('blood_component_id', $record->blood_component_id)->exists()) {
                throw ValidationException::withMessages(['component' => ['This component already has a transfusion final disposition.']]);
            }

            $componentStatus = match ($unusedComponentDisposition) {
                'returned' => ComponentStatus::Returned,
                'discarded' => ComponentStatus::Discarded,
                default => ComponentStatus::Transfused,
            };

            $status = match ($unusedComponentDisposition) {
                'returned' => TransfusionRecordStatus::ReturnedUnused,
                'discarded' => TransfusionRecordStatus::DiscardedUnused,
                default => TransfusionRecordStatus::Completed,
            };

            $record->component->forceFill([
                'status' => $componentStatus,
            ])->save();

            return TransfusionRecord::query()->create([
                'bedside_checks' => $bedsideChecks,
                'blood_component_id' => $record->blood_component_id,
                'completed_at' => now()->addHour(),
                'final_disposition_at' => now()->addHour(),
                'hospital_blood_request_id' => $record->hospital_blood_request_id,
                'hospital_component_allocation_id' => $record->id,
                'hospital_component_receipt_id' => $lockedReceipt->id,
                'observations' => $observations,
                'outcome' => trim($outcome),
                'patient_specimen_id' => $lockedSpecimen->id,
                'recorded_by' => $nurse->id,
                'started_at' => now(),
                'status' => $status,
                'unused_component_disposition' => $unusedComponentDisposition,
                'verified_at' => now(),
                'verified_by' => $nurse->id,
                'volume_ml' => $volumeMl,
            ]);
        }, attempts: 3);
    }

    /**
     * @return Collection<int, HospitalComponentAllocation>
     */
    public function overdueOutcomeQueue(Hospital $hospital): Collection
    {
        return HospitalComponentAllocation::query()
            ->where('status', HospitalAllocationStatus::Received)
            ->where('issued_at', '<', now()->subDay())
            ->whereHas('bloodRequest', fn ($query) => $query->where('hospital_id', $hospital->id))
            ->whereDoesntHave('component', fn ($query) => $query->whereHas('transfusionRecords'))
            ->with(['bloodRequest', 'component'])
            ->latest()
            ->get();
    }

    private function nextCompatibleTest(HospitalBloodRequest $request): ?CompatibilityTest
    {
        return CompatibilityTest::query()
            ->where('hospital_blood_request_id', $request->id)
            ->where('compatibility_result', CompatibilityResult::Compatible)
            ->where('status', CompatibilityTestStatus::Reviewed)
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhere('valid_until', '>', now()))
            ->whereDoesntHave('component', fn ($query) => $query->where('status', '!=', ComponentStatus::Available))
            ->with('component')
            ->get()
            ->sortBy(fn (CompatibilityTest $test): string => (string) $test->component->expiry_date)
            ->first();
    }

    private function nextEmergencyRelease(HospitalBloodRequest $request): ?EmergencyReleaseAuthorization
    {
        return EmergencyReleaseAuthorization::query()
            ->where('hospital_blood_request_id', $request->id)
            ->whereIn('status', [EmergencyReleaseStatus::Authorized, EmergencyReleaseStatus::Acknowledged])
            ->where('retrospective_completion_due_at', '>', now())
            ->with('component')
            ->get()
            ->sortBy(fn (EmergencyReleaseAuthorization $authorization): string => (string) $authorization->component->expiry_date)
            ->first();
    }

    /** @param array<string, bool> $finalCheck */
    private function assertFinalIssueCheck(array $finalCheck): void
    {
        $required = ['request', 'patient', 'component', 'release', 'compatibility_or_emergency', 'expiry', 'label', 'package', 'staff'];

        foreach ($required as $key) {
            if (($finalCheck[$key] ?? false) !== true) {
                throw ValidationException::withMessages(['final_check' => ['Final issue check is incomplete.']]);
            }
        }
    }

    /** @param array<string, bool> $bedsideChecks */
    private function assertBedsideChecks(array $bedsideChecks): void
    {
        $required = ['right_patient', 'right_component', 'right_request', 'right_time', 'expiry', 'compatibility_or_emergency', 'staff'];

        foreach ($required as $key) {
            if (($bedsideChecks[$key] ?? false) !== true) {
                throw ValidationException::withMessages(['bedside_checks' => ['Bedside verification is incomplete.']]);
            }
        }
    }
}
