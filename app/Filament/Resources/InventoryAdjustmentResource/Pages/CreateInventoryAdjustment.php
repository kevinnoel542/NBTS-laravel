<?php

namespace App\Filament\Resources\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\InventoryAdjustmentResource;
use App\Models\InventoryAdjustment;
use App\Services\InventoryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInventoryAdjustment extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = InventoryAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        app(InventoryService::class)->manualAdjust(
            bloodCenterId: (int) $data['blood_center_id'],
            bloodGroup: $data['blood_group'],
            quantityDelta: (int) $data['quantity_delta'],
            reason: $data['reason'],
            adjustedBy: auth()->user(),
            notes: $data['notes'] ?? null,
            bloodUnitId: isset($data['blood_unit_id']) ? (int) $data['blood_unit_id'] : null,
        );

        return InventoryAdjustment::query()
            ->with(['bloodCenter', 'bloodUnit', 'adjuster'])
            ->where('blood_center_id', $data['blood_center_id'])
            ->where('blood_group', $data['blood_group'])
            ->where('quantity_delta', $data['quantity_delta'])
            ->where('reason', $data['reason'])
            ->latest()
            ->firstOrFail();
    }
}
