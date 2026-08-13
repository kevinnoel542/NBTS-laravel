<?php

namespace App\Services;

use App\BloodGroup;
use App\CompatibilityResult;
use App\CompatibilityTestStatus;
use App\ComponentStatus;
use App\EmergencyReleaseStatus;
use App\HospitalRequestStatus;
use App\HospitalRequestUrgency;
use App\Models\BloodComponent;
use App\Models\CompatibilityTest;
use App\Models\EmergencyReleaseAuthorization;
use App\Models\HospitalBloodRequest;
use App\Models\PatientSpecimen;
use App\Models\User;
use App\PatientSpecimenStatus;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CompatibilityWorkflowService
{
    public function __construct(private HospitalAccessService $hospitalAccess) {}

    public function receiveSpecimen(
        HospitalBloodRequest $request,
        User $actor,
        string $specimenIdentifier,
        string $patientReference,
        string $positiveIdentificationMethod,
        BloodGroup $bloodGroup,
        string $antibodyScreenResult = 'negative',
    ): PatientSpecimen {
        if (! $actor->can(PermissionName::ManageCompatibility->value) && ! $actor->can(PermissionName::ManageHospitalRequests->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot receive patient specimens.']]);
        }

        $request->loadMissing('hospital');
        $this->hospitalAccess->ensure($actor, $request->hospital);

        if (hash('sha256', trim($patientReference)) !== $request->patient_reference_hash) {
            throw ValidationException::withMessages(['patient_reference' => ['The specimen patient identity does not match the request.']]);
        }

        return DB::transaction(function () use ($request, $actor, $specimenIdentifier, $patientReference, $positiveIdentificationMethod, $bloodGroup, $antibodyScreenResult): PatientSpecimen {
            $record = HospitalBloodRequest::query()->lockForUpdate()->findOrFail($request->id);
            $record->forceFill([
                'reviewed_at' => now(),
                'reviewed_by' => $actor->id,
                'status' => HospitalRequestStatus::UnderReview,
            ])->save();

            return PatientSpecimen::query()->create([
                'antibody_screen_result' => $antibodyScreenResult,
                'blood_group' => $bloodGroup,
                'collected_at' => now(),
                'collected_by' => $actor->id,
                'expires_at' => now()->addDays(3),
                'hospital_blood_request_id' => $record->id,
                'hospital_id' => $record->hospital_id,
                'patient_reference' => trim($patientReference),
                'patient_reference_hash' => $record->patient_reference_hash,
                'positive_identification_method' => trim($positiveIdentificationMethod),
                'received_at' => now(),
                'received_by' => $actor->id,
                'specimen_identifier' => trim($specimenIdentifier) !== '' ? trim($specimenIdentifier) : 'PSP-'.Str::upper(Str::random(10)),
                'status' => PatientSpecimenStatus::Received,
            ]);
        }, attempts: 3);
    }

    public function recordCompatibility(
        HospitalBloodRequest $request,
        PatientSpecimen $specimen,
        BloodComponent $component,
        User $performer,
        User $reviewer,
        CompatibilityResult $result,
        string $method = 'gel_card_crossmatch',
        ?string $exceptionReason = null,
    ): CompatibilityTest {
        if (! $performer->can(PermissionName::ManageCompatibility->value) || ! $reviewer->can(PermissionName::ManageCompatibility->value)) {
            throw ValidationException::withMessages(['actor' => ['Compatibility testing requires compatibility authority.']]);
        }

        $request->loadMissing('hospital');
        $this->hospitalAccess->ensure($performer, $request->hospital);
        $this->hospitalAccess->ensure($reviewer, $request->hospital);

        return DB::transaction(function () use ($request, $specimen, $component, $performer, $reviewer, $result, $method, $exceptionReason): CompatibilityTest {
            $lockedRequest = HospitalBloodRequest::query()->lockForUpdate()->findOrFail($request->id);
            $lockedSpecimen = PatientSpecimen::query()->lockForUpdate()->findOrFail($specimen->id);
            $lockedComponent = BloodComponent::query()->lockForUpdate()->findOrFail($component->id);

            $this->assertRoutineCompatibilityInputs($lockedRequest, $lockedSpecimen, $lockedComponent);

            $status = $result === CompatibilityResult::Compatible
                ? CompatibilityTestStatus::Reviewed
                : CompatibilityTestStatus::Rejected;

            return CompatibilityTest::query()->create([
                'abo_rh_confirmation' => $lockedSpecimen->blood_group,
                'antibody_screen_result' => $lockedSpecimen->antibody_screen_result,
                'blood_component_id' => $lockedComponent->id,
                'compatibility_result' => $result,
                'control_result' => 'valid',
                'exception_reason' => $exceptionReason,
                'hospital_blood_request_id' => $lockedRequest->id,
                'instrument_identifier' => 'manual',
                'method' => trim($method),
                'patient_specimen_id' => $lockedSpecimen->id,
                'performed_at' => now(),
                'performed_by' => $performer->id,
                'reagent_lot' => 'construction-lot',
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'status' => $status,
                'valid_until' => now()->addDays(3),
            ]);
        }, attempts: 3);
    }

    public function authorizeEmergencyRelease(
        HospitalBloodRequest $request,
        BloodComponent $component,
        User $actor,
        string $clinicalAuthorizerName,
        string $reason,
        string $riskAcknowledgement,
    ): EmergencyReleaseAuthorization {
        if (! $actor->can(PermissionName::ManageBloodIssue->value) && ! $actor->can(PermissionName::ManageHospitalRequests->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot authorize emergency release.']]);
        }

        $request->loadMissing('hospital');
        $this->hospitalAccess->ensure($actor, $request->hospital);

        if (! in_array($request->urgency, [HospitalRequestUrgency::Emergency, HospitalRequestUrgency::MassiveHaemorrhage], true)) {
            throw ValidationException::withMessages(['urgency' => ['Emergency release requires an emergency request.']]);
        }

        if (mb_strlen(trim($clinicalAuthorizerName)) < 3 || mb_strlen(trim($reason)) < 10 || mb_strlen(trim($riskAcknowledgement)) < 15) {
            throw ValidationException::withMessages(['emergency_release' => ['Emergency release requires named clinical authorization, reason, and risk acknowledgement.']]);
        }

        return DB::transaction(function () use ($request, $component, $actor, $clinicalAuthorizerName, $reason, $riskAcknowledgement): EmergencyReleaseAuthorization {
            $lockedComponent = BloodComponent::query()->lockForUpdate()->findOrFail($component->id);

            if (! $lockedComponent->isAvailableForAllocation()) {
                throw ValidationException::withMessages(['component' => ['Emergency release cannot use unavailable, expired, recalled, held, or already issued stock.']]);
            }

            if ($lockedComponent->component_product_catalog_id !== $request->component_product_catalog_id
                || ($request->requested_blood_group instanceof BloodGroup && $lockedComponent->blood_group !== $request->requested_blood_group)) {
                throw ValidationException::withMessages(['component' => ['Emergency release selected the wrong component for this request.']]);
            }

            return EmergencyReleaseAuthorization::query()->create([
                'acknowledged_at' => now(),
                'acknowledged_by' => $actor->id,
                'authorized_at' => now(),
                'authorized_by' => $actor->id,
                'blood_component_id' => $lockedComponent->id,
                'clinical_authorizer_name' => trim($clinicalAuthorizerName),
                'hospital_blood_request_id' => $request->id,
                'reason' => trim($reason),
                'retrospective_completion_due_at' => now()->addDay(),
                'risk_acknowledgement' => trim($riskAcknowledgement),
                'status' => EmergencyReleaseStatus::Acknowledged,
            ]);
        }, attempts: 3);
    }

    private function assertRoutineCompatibilityInputs(HospitalBloodRequest $request, PatientSpecimen $specimen, BloodComponent $component): void
    {
        if ($specimen->hospital_blood_request_id !== $request->id
            || $specimen->hospital_id !== $request->hospital_id
            || $specimen->patient_reference_hash !== $request->patient_reference_hash) {
            throw ValidationException::withMessages(['specimen' => ['The specimen is not linked to the selected patient request.']]);
        }

        if ($specimen->status !== PatientSpecimenStatus::Received || $specimen->expires_at->isPast()) {
            throw ValidationException::withMessages(['specimen' => ['The patient specimen is not usable for compatibility testing.']]);
        }

        if ($component->component_product_catalog_id !== $request->component_product_catalog_id
            || $component->status !== ComponentStatus::Available
            || $component->expiry_date->isPast()) {
            throw ValidationException::withMessages(['component' => ['The component is not available and approved for compatibility testing.']]);
        }

        if ($request->requested_blood_group instanceof BloodGroup
            && $component->blood_group !== $request->requested_blood_group) {
            throw ValidationException::withMessages(['component' => ['The component blood group does not match the request.']]);
        }

        if ($specimen->blood_group !== $component->blood_group) {
            throw ValidationException::withMessages(['component' => ['The component is not ABO/Rh compatible with the patient specimen.']]);
        }
    }
}
