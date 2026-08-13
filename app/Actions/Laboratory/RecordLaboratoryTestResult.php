<?php

namespace App\Actions\Laboratory;

use App\LaboratoryTestInterpretation;
use App\LaboratoryTestOrderStatus;
use App\LaboratoryTestResultStatus;
use App\LaboratoryTestRunStatus;
use App\Models\LaboratoryEquipment;
use App\Models\LaboratoryQualityControlRun;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratoryTestOrder;
use App\Models\LaboratoryTestResult;
use App\Models\LaboratoryTestRun;
use App\Models\User;
use App\PermissionName;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class RecordLaboratoryTestResult
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function handle(
        User $actor,
        LaboratoryTestOrder $order,
        LaboratoryQualityControlRun $qualityControlRun,
        LaboratoryTestInterpretation $interpretation,
        string $resultValue,
        ?LaboratoryEquipment $equipment = null,
        ?LaboratoryReagentLot $reagentLot = null,
        array $rawPayload = [],
        ?string $comments = null,
    ): LaboratoryTestResult {
        return DB::transaction(function () use ($actor, $order, $qualityControlRun, $interpretation, $resultValue, $equipment, $reagentLot, $rawPayload, $comments): LaboratoryTestResult {
            $record = LaboratoryTestOrder::query()
                ->with(['testCatalog', 'receipt.collectionEpisode.bloodCenter'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            Gate::forUser($actor)->authorize('view', $record->receipt->collectionEpisode);

            if (! $actor->can(PermissionName::RecordLaboratoryTests->value)) {
                throw ValidationException::withMessages(['laboratory' => ['This account cannot record laboratory results.']]);
            }

            if (! in_array($record->status, [LaboratoryTestOrderStatus::Ordered, LaboratoryTestOrderStatus::InProgress], true)) {
                throw ValidationException::withMessages(['order' => ['Only ordered or in-progress laboratory tests can receive a result.']]);
            }

            if ($qualityControlRun->laboratory_test_catalog_id !== $record->laboratory_test_catalog_id || ! $qualityControlRun->permitsResultUse()) {
                throw ValidationException::withMessages(['quality_control' => ['A passed quality-control run for this test is required before recording a usable result.']]);
            }

            if ($equipment !== null && ! $equipment->permitsTestingUse()) {
                throw ValidationException::withMessages(['equipment' => ['Laboratory equipment is not valid for result use.']]);
            }

            if ($reagentLot !== null && (! $reagentLot->permitsTestingUse() || ! in_array($reagentLot->laboratory_test_catalog_id, [null, $record->laboratory_test_catalog_id], true))) {
                throw ValidationException::withMessages(['reagent' => ['A usable validated reagent lot for this test is required.']]);
            }

            $run = LaboratoryTestRun::query()->create([
                'laboratory_test_order_id' => $record->id,
                'laboratory_test_catalog_id' => $record->laboratory_test_catalog_id,
                'laboratory_equipment_id' => $equipment?->id,
                'laboratory_reagent_lot_id' => $reagentLot?->id,
                'operator_id' => $actor->id,
                'method_version' => $record->testCatalog->algorithm_version,
                'status' => LaboratoryTestRunStatus::Completed,
                'started_at' => now(),
                'ended_at' => now(),
                'raw_payload' => $rawPayload,
                'comments' => $comments,
            ]);

            $result = LaboratoryTestResult::query()->create([
                'laboratory_test_order_id' => $record->id,
                'laboratory_test_run_id' => $run->id,
                'laboratory_test_catalog_id' => $record->laboratory_test_catalog_id,
                'laboratory_quality_control_run_id' => $qualityControlRun->id,
                'entered_by' => $actor->id,
                'result_value' => trim($resultValue),
                'interpretation' => $interpretation,
                'status' => LaboratoryTestResultStatus::Preliminary,
                'is_release_blocking' => $record->testCatalog->blocksInterpretation($interpretation->value),
                'resulted_at' => now(),
                'comments' => $comments,
            ]);

            $record->forceFill(['status' => LaboratoryTestOrderStatus::Resulted])->save();

            $this->auditLogger->record($actor, 'laboratory.result_recorded', $result, $record->receipt->collectionEpisode->bloodCenter, [
                'catalog_code' => $record->testCatalog->code,
                'interpretation' => $interpretation->value,
                'release_blocking' => $result->is_release_blocking,
                'specimen_identifier' => $record->receipt->specimen->specimen_identifier,
            ]);

            return $result->fresh(['order', 'run', 'testCatalog', 'qualityControlRun']);
        }, attempts: 3);
    }
}
