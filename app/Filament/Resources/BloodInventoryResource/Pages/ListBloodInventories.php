<?php

namespace App\Filament\Resources\BloodInventoryResource\Pages;

use App\Filament\Resources\BloodInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBloodInventories extends ListRecords
{
    protected static string $resource = BloodInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
