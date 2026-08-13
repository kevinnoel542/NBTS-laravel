<?php

namespace App\Services;

use App\HospitalRequestStatus;
use App\HospitalRequestUrgency;
use App\HospitalStatus;
use App\Models\ComponentProductCatalog;
use App\Models\Hospital;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalService;
use App\Models\User;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class HospitalRequestService
{
    public function __construct(private HospitalAccessService $hospitalAccess) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(
        Hospital $hospital,
        ?HospitalService $service,
        ComponentProductCatalog $catalog,
        User $requester,
        array $data,
    ): HospitalBloodRequest {
        if (! $requester->can(PermissionName::ManageHospitalRequests->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot create hospital blood requests.']]);
        }

        $this->hospitalAccess->ensure($requester, $hospital);

        if ($hospital->status !== HospitalStatus::Active || ($service instanceof HospitalService && $service->hospital_id !== $hospital->id)) {
            throw ValidationException::withMessages(['hospital' => ['The selected hospital or service is not available for requests.']]);
        }

        $patientReference = trim((string) ($data['patient_reference'] ?? ''));
        $diagnosis = trim((string) ($data['diagnosis'] ?? ''));
        $indication = trim((string) ($data['indication'] ?? ''));
        $quantity = (int) ($data['quantity_requested'] ?? 0);
        $urgency = HospitalRequestUrgency::from((string) ($data['urgency'] ?? HospitalRequestUrgency::Routine->value));
        $hemoglobin = isset($data['hemoglobin_g_dl']) ? (float) $data['hemoglobin_g_dl'] : null;
        $activeBleeding = (bool) ($data['active_bleeding'] ?? false);
        $sourceMode = (string) ($data['source_mode'] ?? 'electronic');
        $overrideReason = trim((string) ($data['override_reason'] ?? ''));

        if ($patientReference === '' || $diagnosis === '' || $indication === '' || $quantity < 1 || empty($data['patient_birth_year']) || empty($data['patient_gender'])) {
            throw ValidationException::withMessages(['request' => ['Hospital requests require minimum patient identity, diagnosis, indication, component, quantity, and required time.']]);
        }

        if ($this->requiresGuidanceOverride($hemoglobin, $activeBleeding, $urgency) && mb_strlen($overrideReason) < 10) {
            throw ValidationException::withMessages(['override_reason' => ['Requests outside approved guidance require an override reason.']]);
        }

        return DB::transaction(function () use ($hospital, $service, $catalog, $requester, $data, $patientReference, $diagnosis, $indication, $quantity, $urgency, $hemoglobin, $activeBleeding, $sourceMode, $overrideReason): HospitalBloodRequest {
            return HospitalBloodRequest::query()->create([
                'active_bleeding' => $activeBleeding,
                'attachments' => $data['attachments'] ?? [],
                'component_product_catalog_id' => $catalog->id,
                'diagnosis' => $diagnosis,
                'guidance_snapshot' => [
                    'version' => 'patient-blood-management-v1',
                    'override_required' => $this->requiresGuidanceOverride($hemoglobin, $activeBleeding, $urgency),
                ],
                'hemoglobin_g_dl' => $hemoglobin,
                'hospital_id' => $hospital->id,
                'hospital_service_id' => $service?->id,
                'indication' => $indication,
                'notes' => $data['notes'] ?? null,
                'observations' => $data['observations'] ?? [],
                'override_reason' => $overrideReason !== '' ? $overrideReason : null,
                'patient_birth_year' => (int) $data['patient_birth_year'],
                'patient_gender' => (string) $data['patient_gender'],
                'patient_reference' => $patientReference,
                'patient_reference_hash' => hash('sha256', $patientReference),
                'quantity_requested' => $quantity,
                'request_reference' => 'HBR-'.Str::upper(Str::random(10)),
                'requested_blood_group' => $data['requested_blood_group'] ?? null,
                'requested_by' => $requester->id,
                'required_at' => $data['required_at'] ?? now()->addHours(6),
                'source_mode' => $sourceMode,
                'status' => $sourceMode === 'downtime_paper'
                    ? HospitalRequestStatus::DowntimeCaptured
                    : HospitalRequestStatus::Submitted,
                'submitted_at' => now(),
                'urgency' => $urgency,
            ]);
        }, attempts: 3);
    }

    private function requiresGuidanceOverride(?float $hemoglobin, bool $activeBleeding, HospitalRequestUrgency $urgency): bool
    {
        if ($urgency !== HospitalRequestUrgency::Routine || $activeBleeding) {
            return false;
        }

        return $hemoglobin !== null && $hemoglobin >= 10.0;
    }
}
