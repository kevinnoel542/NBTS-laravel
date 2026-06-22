<?php

namespace App\Services;

use App\Models\BloodInventory;
use App\Models\Campaign;
use App\Models\LowStockAlert;

class LowStockService
{
    public function evaluate(BloodInventory $inventory): ?LowStockAlert
    {
        if ($inventory->available_units >= $inventory->minimum_threshold) {
            LowStockAlert::where('blood_center_id', $inventory->blood_center_id)
                ->where('blood_group', $inventory->blood_group)
                ->whereIn('status', ['open', 'notified', 'campaign_created'])
                ->update(['status' => 'resolved', 'resolved_at' => now()]);

            return null;
        }

        $alert = LowStockAlert::where('blood_center_id', $inventory->blood_center_id)
            ->where('blood_group', $inventory->blood_group)
            ->whereIn('status', ['open', 'notified', 'campaign_created'])
            ->latest()
            ->first();

        if ($alert) {
            $alert->update([
                'available_units' => $inventory->available_units,
                'minimum_threshold' => $inventory->minimum_threshold,
            ]);

            return $alert;
        }

        return LowStockAlert::create([
            'blood_center_id' => $inventory->blood_center_id,
            'blood_group' => $inventory->blood_group,
            'available_units' => $inventory->available_units,
            'minimum_threshold' => $inventory->minimum_threshold,
            'status' => 'open',
        ]);
    }

    public function createEmergencyCampaign(LowStockAlert $alert): Campaign
    {
        $center = $alert->bloodCenter;

        $existingCampaign = Campaign::where('low_stock_alert_id', $alert->id)->first();

        if ($existingCampaign) {
            return $existingCampaign;
        }

        $campaign = Campaign::create([
            'title' => 'Emergency ' . $alert->blood_group . ' Blood Appeal',
            'description' => 'Urgent blood donation appeal for ' . $alert->blood_group . ' at ' . $center?->name . '.',
            'start_date' => now(),
            'end_date' => now()->addDays(14),
            'blood_center_id' => $alert->blood_center_id,
            'location' => $center?->address,
            'status' => 'upcoming',
            'campaign_type' => 'emergency',
            'target_blood_group' => $alert->blood_group,
            'low_stock_alert_id' => $alert->id,
        ]);

        $alert->update(['status' => 'campaign_created']);

        return $campaign;
    }
}
