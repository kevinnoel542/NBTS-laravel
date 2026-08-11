<?php

namespace App\Actions\Inventory;

use App\BloodUnitStatus;
use App\Exceptions\InvalidBloodUnitTransition;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Services\InventoryStockAlertService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use LogicException;

final readonly class TransitionBloodUnit
{
    public function __construct(
        private AuditLogger $auditLogger,
        private InventoryStockAlertService $inventoryStockAlertService,
    ) {}

    public function execute(
        BloodUnit $bloodUnit,
        BloodUnitStatus $status,
        User $actor,
        ?string $notes = null,
    ): BloodUnit {
        return DB::transaction(function () use ($bloodUnit, $status, $actor, $notes): BloodUnit {
            $lockedUnit = BloodUnit::query()
                ->lockForUpdate()
                ->whereKey($bloodUnit->getKey())
                ->firstOrFail();

            Gate::forUser($actor)->authorize('transition', $lockedUnit);

            $previousStatus = $lockedUnit->status;

            if (! $previousStatus->canTransitionTo($status)) {
                throw InvalidBloodUnitTransition::from($previousStatus, $status);
            }

            BloodInventory::query()->firstOrCreate(
                [
                    'blood_center_id' => $lockedUnit->blood_center_id,
                    'blood_group' => $lockedUnit->blood_group,
                ],
                [
                    'available_units' => 0,
                    'minimum_threshold' => 5,
                    'reserved_units' => 0,
                ],
            );

            $inventory = BloodInventory::query()
                ->lockForUpdate()
                ->where('blood_center_id', $lockedUnit->blood_center_id)
                ->where('blood_group', $lockedUnit->blood_group)
                ->firstOrFail();

            $availableDelta = (int) $status->contributesToAvailableInventory()
                - (int) $previousStatus->contributesToAvailableInventory();
            $reservedDelta = (int) ($status === BloodUnitStatus::Reserved)
                - (int) ($previousStatus === BloodUnitStatus::Reserved);
            $newAvailableUnits = $inventory->available_units + $availableDelta;
            $newReservedUnits = $inventory->reserved_units + $reservedDelta;

            if ($newAvailableUnits < 0 || $newReservedUnits < 0) {
                throw new LogicException('Blood inventory cannot transition below zero.');
            }

            $lockedUnit->forceFill([
                'handled_by' => $actor->id,
                'status' => $status,
            ])->save();

            $inventory->forceFill([
                'available_units' => $newAvailableUnits,
                'reserved_units' => $newReservedUnits,
            ])->save();

            if ($availableDelta !== 0 || $reservedDelta !== 0) {
                InventoryAdjustment::query()->create([
                    'adjusted_by' => $actor->id,
                    'blood_center_id' => $lockedUnit->blood_center_id,
                    'blood_group' => $lockedUnit->blood_group,
                    'blood_unit_id' => $lockedUnit->id,
                    'notes' => $notes,
                    'quantity_delta' => $availableDelta,
                    'reason' => "unit_status_{$status->value}",
                    'reserved_quantity_delta' => $reservedDelta,
                ]);
            }

            $lowStockAlert = $this->inventoryStockAlertService->evaluate($inventory->refresh());

            $this->auditLogger->record(
                actor: $actor,
                action: 'blood_units.status_changed',
                subject: $lockedUnit,
                bloodCenter: $lockedUnit->bloodCenter,
                metadata: [
                    'available_quantity_delta' => $availableDelta,
                    'available_units' => $inventory->available_units,
                    'from_status' => $previousStatus->value,
                    'low_stock_alert_id' => $lowStockAlert?->id,
                    'reserved_quantity_delta' => $reservedDelta,
                    'reserved_units' => $inventory->reserved_units,
                    'to_status' => $status->value,
                ],
            );

            return $lockedUnit->refresh()->load(['bloodCenter', 'inventoryAdjustments']);
        }, attempts: 3);
    }
}
