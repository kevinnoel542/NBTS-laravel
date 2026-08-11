<?php

namespace App\Services;

use App\LowStockAlertStatus;
use App\Models\BloodInventory;
use App\Models\LowStockAlert;

final class InventoryStockAlertService
{
    public function evaluate(BloodInventory $inventory): ?LowStockAlert
    {
        $activeAlert = LowStockAlert::query()
            ->where('blood_center_id', $inventory->blood_center_id)
            ->where('blood_group', $inventory->blood_group)
            ->whereIn('status', [
                LowStockAlertStatus::Open,
                LowStockAlertStatus::Notified,
                LowStockAlertStatus::CampaignCreated,
            ])
            ->lockForUpdate()
            ->latest('id')
            ->first();

        if ($inventory->available_units < $inventory->minimum_threshold) {
            if (! $activeAlert) {
                return LowStockAlert::query()->create([
                    'available_units' => $inventory->available_units,
                    'blood_center_id' => $inventory->blood_center_id,
                    'blood_group' => $inventory->blood_group,
                    'minimum_threshold' => $inventory->minimum_threshold,
                    'status' => LowStockAlertStatus::Open,
                ]);
            }

            $activeAlert->forceFill([
                'available_units' => $inventory->available_units,
                'minimum_threshold' => $inventory->minimum_threshold,
            ])->save();

            return $activeAlert->refresh();
        }

        if ($activeAlert) {
            $activeAlert->forceFill([
                'available_units' => $inventory->available_units,
                'resolved_at' => now(),
                'status' => LowStockAlertStatus::Resolved,
            ])->save();

            return $activeAlert->refresh();
        }

        return null;
    }
}
