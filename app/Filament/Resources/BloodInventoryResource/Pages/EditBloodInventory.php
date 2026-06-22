<?php

namespace App\Filament\Resources\BloodInventoryResource\Pages;

use App\Filament\Resources\BloodInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBloodInventory extends EditRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = BloodInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
