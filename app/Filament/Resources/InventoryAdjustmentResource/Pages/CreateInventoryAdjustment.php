<?php

namespace App\Filament\Resources\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\InventoryAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryAdjustment extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = InventoryAdjustmentResource::class;
}
