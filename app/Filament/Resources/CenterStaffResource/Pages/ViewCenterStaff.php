<?php

namespace App\Filament\Resources\CenterStaffResource\Pages;

use App\Filament\Resources\CenterStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCenterStaff extends ViewRecord
{
    protected static string $resource = CenterStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
