<?php

namespace App\Actions\Inventory;

use App\BloodUnitStatus;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Services\BloodUnitQuarantineService;
use App\Services\InventoryStockAlertService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ReconcileInventory
{
    public function __construct(
        private AuditLogger $auditLogger,
        private BloodUnitQuarantineService $bloodUnitQuarantineService,
        private InventoryStockAlertService $inventoryStockAlertService,
    ) {}

    /**
     * @return array{
     *     inventory_id: int,
     *     current_available: int,
     *     expected_available: int,
     *     current_reserved: int,
     *     expected_reserved: int,
     *     available_delta: int,
     *     reserved_delta: int,
     *     mismatch: bool,
     *     repaired: bool,
     *     adjustment_id: int|null
     * }
     */
    public function execute(
        BloodInventory $inventory,
        User $actor,
        bool $repair = false,
        ?string $reason = null,
    ): array {
        $reason = $reason === null ? null : trim($reason);

        if ($repair && mb_strlen((string) $reason) < 10) {
            throw ValidationException::withMessages([
                'inventoryAdjustmentReason' => [__('console.workflow.reason_required')],
            ]);
        }

        return DB::transaction(function () use ($inventory, $actor, $repair, $reason): array {
            $unitStatuses = BloodUnit::query()
                ->with('quarantine')
                ->where('blood_center_id', $inventory->blood_center_id)
                ->where('blood_group', $inventory->blood_group)
                ->lockForUpdate()
                ->get();

            $lockedInventory = BloodInventory::query()
                ->with('bloodCenter')
                ->lockForUpdate()
                ->findOrFail($inventory->id);

            Gate::forUser($actor)->authorize($repair ? 'update' : 'view', $lockedInventory);

            $currentAvailable = $lockedInventory->available_units;
            $currentReserved = $lockedInventory->reserved_units;
            $expectedAvailable = $unitStatuses
                ->filter(fn (BloodUnit $bloodUnit): bool => $this->bloodUnitQuarantineService->canContributeToAvailableStock($bloodUnit))
                ->count();
            $expectedReserved = $unitStatuses
                ->where('status', BloodUnitStatus::Reserved)
                ->count();
            $availableDelta = $expectedAvailable - $currentAvailable;
            $reservedDelta = $expectedReserved - $currentReserved;
            $mismatch = $availableDelta !== 0 || $reservedDelta !== 0;
            $adjustment = null;

            if ($repair && $mismatch) {
                $lockedInventory->forceFill([
                    'available_units' => $expectedAvailable,
                    'reserved_units' => $expectedReserved,
                ])->save();

                $adjustment = InventoryAdjustment::query()->create([
                    'adjusted_by' => $actor->id,
                    'blood_center_id' => $lockedInventory->blood_center_id,
                    'blood_group' => $lockedInventory->blood_group,
                    'notes' => $reason,
                    'quantity_delta' => $availableDelta,
                    'reason' => 'inventory_reconciliation',
                    'reserved_quantity_delta' => $reservedDelta,
                ]);

                $this->inventoryStockAlertService->evaluate($lockedInventory->refresh());
            }

            if ($repair) {
                $this->auditLogger->record(
                    actor: $actor,
                    action: 'inventory.reconciled',
                    subject: $adjustment ?? $lockedInventory,
                    bloodCenter: $lockedInventory->bloodCenter,
                    metadata: [
                        'available_delta' => $availableDelta,
                        'expected_available' => $expectedAvailable,
                        'expected_reserved' => $expectedReserved,
                        'reason' => $reason,
                        'reserved_delta' => $reservedDelta,
                    ],
                );
            }

            return [
                'inventory_id' => $lockedInventory->id,
                'current_available' => $currentAvailable,
                'expected_available' => $expectedAvailable,
                'current_reserved' => $currentReserved,
                'expected_reserved' => $expectedReserved,
                'available_delta' => $availableDelta,
                'reserved_delta' => $reservedDelta,
                'mismatch' => $mismatch,
                'repaired' => $repair && $mismatch,
                'adjustment_id' => $adjustment?->id,
            ];
        }, attempts: 3);
    }
}
