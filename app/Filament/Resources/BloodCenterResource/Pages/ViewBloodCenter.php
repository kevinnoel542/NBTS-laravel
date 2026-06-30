<?php

namespace App\Filament\Resources\BloodCenterResource\Pages;

use App\Filament\Resources\BloodCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBloodCenter extends ViewRecord
{
    protected static string $resource = BloodCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
