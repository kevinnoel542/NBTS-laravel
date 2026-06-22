<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\BloodInventoryResource;
use App\Http\Resources\BloodUnitResource;
use App\Http\Resources\LowStockAlertResource;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\LowStockAlert;
use App\Services\InventoryService;
use App\Services\LowStockService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function inventory(Request $request)
    {
        if (!$request->user()->can('inventory.view')) {
            abort(403);
        }

        return BloodInventoryResource::collection(
            BloodInventory::with('bloodCenter')->orderBy('blood_center_id')->orderBy('blood_group')->get()
        );
    }

    public function units(Request $request)
    {
        if (!$request->user()->can('inventory.view')) {
            abort(403);
        }

        return BloodUnitResource::collection(
            BloodUnit::with('bloodCenter')->latest()->paginate(50)
        );
    }

    public function transitionUnit(BloodUnit $unit, Request $request, InventoryService $inventoryService)
    {
        if (!$request->user()->can('inventory.manage')) {
            abort(403);
        }

        $data = $request->validate([
            'status' => 'required|string|in:collected,testing,available,reserved,transferred,used,rejected,expired,discarded',
            'notes' => 'nullable|string',
        ]);

        return new BloodUnitResource($inventoryService->transitionUnit($unit, $data['status'], $request->user(), $data['notes'] ?? null)->load('bloodCenter'));
    }

    public function adjust(Request $request, InventoryService $inventoryService)
    {
        if (!$request->user()->can('inventory.manage')) {
            abort(403);
        }

        $data = $request->validate([
            'blood_center_id' => 'required|exists:blood_centers,id',
            'blood_group' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'quantity_delta' => 'required|integer',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return new BloodInventoryResource(
            $inventoryService->manualAdjust($data['blood_center_id'], $data['blood_group'], $data['quantity_delta'], $data['reason'], $request->user(), $data['notes'] ?? null)->load('bloodCenter')
        );
    }

    public function expireDue(Request $request, InventoryService $inventoryService)
    {
        if (!$request->user()->can('inventory.manage')) {
            abort(403);
        }

        return response()->json(['expired_units' => $inventoryService->expireDueUnits($request->user())]);
    }

    public function lowStockAlerts(Request $request)
    {
        if (!$request->user()->can('inventory.view')) {
            abort(403);
        }

        return LowStockAlertResource::collection(
            LowStockAlert::with('bloodCenter')->whereIn('status', ['open', 'notified', 'campaign_created'])->latest()->get()
        );
    }

    public function createEmergencyCampaign(LowStockAlert $alert, Request $request, LowStockService $lowStockService)
    {
        if (!$request->user()->can('campaigns.manage')) {
            abort(403);
        }

        return response()->json(['data' => $lowStockService->createEmergencyCampaign($alert)], 201);
    }
}
