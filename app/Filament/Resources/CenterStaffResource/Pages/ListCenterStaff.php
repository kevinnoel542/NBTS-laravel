<?php

namespace App\Filament\Resources\CenterStaffResource\Pages;

use App\Filament\Resources\CenterStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCenterStaff extends ListRecords
{
    protected static string $resource = CenterStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
