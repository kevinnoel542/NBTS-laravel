<?php

namespace App\Actions\Inventory;

use App\Data\AdjustInventoryData;
use App\Models\BloodInventory;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Services\InventoryStockAlertService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class AdjustInventory
{
    public function __construct(
        private AuditLogger $auditLogger,
        private InventoryStockAlertService $inventoryStockAlertService,
    ) {}

    public function execute(BloodInventory $inventory, AdjustInventoryData $data, User $actor): InventoryAdjustment
    {
        $reason = trim($data->reason);

        if ($data->availableDelta === 0 && $data->reservedDelta === 0) {
            throw ValidationException::withMessages([
                'inventoryAvailableDelta' => [__('console.inventory.non_zero_adjustment_required')],
            ]);
        }

        if (mb_strlen($reason) < 10) {
            throw ValidationException::withMessages([
                'inventoryAdjustmentReason' => [__('console.workflow.reason_required')],
            ]);
        }

        return DB::transaction(function () use ($inventory, $data, $actor, $reason): InventoryAdjustment {
            $lockedInventory = BloodInventory::query()
                ->with('bloodCenter')
                ->lockForUpdate()
                ->findOrFail($inventory->id);

            Gate::forUser($actor)->authorize('update', $lockedInventory);

            $newAvailableUnits = $lockedInventory->available_units + $data->availableDelta;
            $newReservedUnits = $lockedInventory->reserved_units + $data->reservedDelta;

            if ($newAvailableUnits < 0 || $newReservedUnits < 0) {
                throw ValidationException::withMessages([
                    'inventoryAvailableDelta' => [__('console.inventory.negative_balance_blocked')],
                ]);
            }

            $lockedInventory->forceFill([
                'available_units' => $newAvailableUnits,
                'reserved_units' => $newReservedUnits,
            ])->save();

            $adjustment = InventoryAdjustment::query()->create([
                'adjusted_by' => $actor->id,
                'blood_center_id' => $lockedInventory->blood_center_id,
                'blood_group' => $lockedInventory->blood_group,
                'notes' => filled($data->notes) ? trim((string) $data->notes) : null,
                'quantity_delta' => $data->availableDelta,
                'reason' => $reason,
                'reserved_quantity_delta' => $data->reservedDelta,
            ]);

            $lowStockAlert = $this->inventoryStockAlertService->evaluate($lockedInventory->refresh());

            $this->auditLogger->record(
                actor: $actor,
                action: 'inventory.manually_adjusted',
                subject: $adjustment,
                bloodCenter: $lockedInventory->bloodCenter,
                metadata: [
                    'available_quantity_delta' => $data->availableDelta,
                    'available_units' => $newAvailableUnits,
                    'low_stock_alert_id' => $lowStockAlert?->id,
                    'reason' => $reason,
                    'reserved_quantity_delta' => $data->reservedDelta,
                    'reserved_units' => $newReservedUnits,
                ],
            );

            return $adjustment->load(['adjuster', 'bloodCenter']);
        }, attempts: 3);
    }
}
