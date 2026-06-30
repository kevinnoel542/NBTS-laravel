<?php

namespace App\Filament\Resources\BloodUnitResource\Pages;

use App\Filament\Resources\BloodUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBloodUnit extends ViewRecord
{
    protected static string $resource = BloodUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
