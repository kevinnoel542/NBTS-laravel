<?php

namespace App\Services;

use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\InventoryAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(private readonly LowStockService $lowStockService)
    {
    }

    public function createUnitFromDonation(Donation $donation, ?User $handledBy = null): BloodUnit
    {
        return DB::transaction(function () use ($donation, $handledBy): BloodUnit {
            $unit = BloodUnit::firstOrCreate(
                ['donation_id' => $donation->id],
                [
                    'unit_number' => $this->generateUnitNumber(),
                    'donor_id' => $donation->user_id,
                    'blood_center_id' => $donation->blood_center_id,
                    'blood_group' => $donation->blood_group,
                    'collection_date' => $donation->donation_date,
                    'expiry_date' => $donation->donation_date->copy()->addDays(35),
                    'status' => 'available',
                    'current_location' => $donation->bloodCenter?->name,
                    'handled_by' => $handledBy?->id,
                ]
            );

            if ($unit->wasRecentlyCreated) {
                $this->adjustInventory($unit, 1, 'donation_collected', $handledBy);
            }

            return $unit;
        });
    }

    public function transitionUnit(BloodUnit $unit, string $status, ?User $handledBy = null, ?string $notes = null): BloodUnit
    {
        $oldStatus = $unit->status;

        if ($oldStatus === $status) {
            return $unit;
        }

        return DB::transaction(function () use ($unit, $status, $handledBy, $notes, $oldStatus): BloodUnit {
            $delta = $this->availableDelta($oldStatus, $status);

            $unit->update([
                'status' => $status,
                'handled_by' => $handledBy?->id,
            ]);

            if ($delta !== 0) {
                $this->adjustInventory($unit, $delta, 'unit_status_' . $status, $handledBy, $notes);
            }

            return $unit->refresh();
        });
    }

    public function manualAdjust(int $bloodCenterId, string $bloodGroup, int $quantityDelta, string $reason, ?User $adjustedBy = null, ?string $notes = null, ?int $bloodUnitId = null): BloodInventory
    {
        return DB::transaction(function () use ($bloodCenterId, $bloodGroup, $quantityDelta, $reason, $adjustedBy, $notes, $bloodUnitId): BloodInventory {
            $inventory = $this->inventoryRow($bloodCenterId, $bloodGroup);
            $newAvailable = $inventory->available_units + $quantityDelta;

            if ($newAvailable < 0) {
                throw ValidationException::withMessages([
                    'quantity_delta' => ['Inventory cannot go below zero.'],
                ]);
            }

            $inventory->update(['available_units' => $newAvailable]);

            InventoryAdjustment::create([
                'blood_center_id' => $bloodCenterId,
                'blood_unit_id' => $bloodUnitId,
                'blood_group' => $bloodGroup,
                'quantity_delta' => $quantityDelta,
                'reason' => $reason,
                'notes' => $notes,
                'adjusted_by' => $adjustedBy?->id,
            ]);

            $this->lowStockService->evaluate($inventory->refresh());

            return $inventory;
        });
    }

    public function expireDueUnits(?User $handledBy = null): int
    {
        $count = 0;

        BloodUnit::whereIn('status', ['collected', 'testing', 'available', 'reserved'])
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->get()
            ->each(function (BloodUnit $unit) use (&$count, $handledBy): void {
                $this->transitionUnit($unit, 'expired', $handledBy, 'Automatically expired by inventory expiry sweep.');
                $count++;
            });

        return $count;
    }

    private function adjustInventory(BloodUnit $unit, int $quantityDelta, string $reason, ?User $adjustedBy = null, ?string $notes = null): BloodInventory
    {
        $inventory = $this->inventoryRow($unit->blood_center_id, $unit->blood_group);
        $newAvailable = max(0, $inventory->available_units + $quantityDelta);
        $inventory->update(['available_units' => $newAvailable]);

        InventoryAdjustment::create([
            'blood_center_id' => $unit->blood_center_id,
            'blood_unit_id' => $unit->id,
            'adjusted_by' => $adjustedBy?->id,
            'blood_group' => $unit->blood_group,
            'quantity_delta' => $quantityDelta,
            'reason' => $reason,
            'notes' => $notes,
        ]);

        $this->lowStockService->evaluate($inventory->refresh());

        return $inventory;
    }

    private function inventoryRow(int $bloodCenterId, string $bloodGroup): BloodInventory
    {
        return BloodInventory::firstOrCreate(
            ['blood_center_id' => $bloodCenterId, 'blood_group' => $bloodGroup],
            ['available_units' => 0, 'reserved_units' => 0, 'minimum_threshold' => 5]
        );
    }

    private function availableDelta(string $oldStatus, string $newStatus): int
    {
        $oldAvailable = $oldStatus === 'available';
        $newAvailable = $newStatus === 'available';

        return match (true) {
            $oldAvailable && !$newAvailable => -1,
            !$oldAvailable && $newAvailable => 1,
            default => 0,
        };
    }

    private function generateUnitNumber(): string
    {
        do {
            $unitNumber = 'BU-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (BloodUnit::where('unit_number', $unitNumber)->exists());

        return $unitNumber;
    }
}
