<?php

namespace App\Actions\Laboratory;

use App\LaboratoryQualityControlStatus;
use App\LaboratoryQualityEventStatus;
use App\LaboratoryQualityEventType;
use App\LaboratoryQualitySeverity;
use App\Models\LaboratoryEquipment;
use App\Models\LaboratoryQualityControlRun;
use App\Models\LaboratoryQualityEvent;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratoryTestCatalog;
use App\Models\User;
use App\PermissionName;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class RecordLaboratoryQualityControl
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $expectedResults
     * @param  array<string, mixed>  $observedResults
     */
    public function handle(
        User $actor,
        LaboratoryTestCatalog $catalog,
        LaboratoryQualityControlStatus $status,
        array $expectedResults,
        array $observedResults,
        ?LaboratoryEquipment $equipment = null,
        ?LaboratoryReagentLot $reagentLot = null,
        ?string $controlLot = null,
        ?string $failureReason = null,
    ): LaboratoryQualityControlRun {
        Gate::forUser($actor)->authorize(PermissionName::RecordLaboratoryTests->value);

        if (! $catalog->is_active) {
            throw ValidationException::withMessages(['catalog' => ['Quality control cannot be recorded against an inactive test catalog.']]);
        }

        if ($status !== LaboratoryQualityControlStatus::Passed && trim((string) $failureReason) === '') {
            throw ValidationException::withMessages(['failure_reason' => ['Failed or invalid quality control requires a reason.']]);
        }

        $run = LaboratoryQualityControlRun::query()->create([
            'laboratory_test_catalog_id' => $catalog->id,
            'laboratory_equipment_id' => $equipment?->id,
            'laboratory_reagent_lot_id' => $reagentLot?->id,
            'performed_by' => $actor->id,
            'status' => $status,
            'control_lot' => $controlLot,
            'expected_results' => $expectedResults,
            'observed_results' => $observedResults,
            'performed_at' => now(),
            'failure_reason' => $failureReason,
        ]);

        if (! $status->permitsResultUse()) {
            LaboratoryQualityEvent::query()->create([
                'laboratory_test_catalog_id' => $catalog->id,
                'laboratory_equipment_id' => $equipment?->id,
                'laboratory_reagent_lot_id' => $reagentLot?->id,
                'opened_by' => $actor->id,
                'type' => LaboratoryQualityEventType::Deviation,
                'severity' => LaboratoryQualitySeverity::High,
                'status' => LaboratoryQualityEventStatus::Open,
                'title' => 'Laboratory QC '.$status->value,
                'description' => trim((string) $failureReason),
                'affected_identifiers' => [$catalog->code],
                'opened_at' => now(),
            ]);
        }

        $this->auditLogger->record($actor, 'laboratory.qc_recorded', $run, metadata: [
            'catalog_code' => $catalog->code,
            'status' => $status->value,
            'equipment_id' => $equipment?->id,
            'reagent_lot_id' => $reagentLot?->id,
        ]);

        return $run->fresh(['testCatalog', 'equipment', 'reagentLot']);
    }
}
