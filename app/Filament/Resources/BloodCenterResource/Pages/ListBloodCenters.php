<?php

namespace App\Filament\Resources\BloodCenterResource\Pages;

use App\Filament\Resources\BloodCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBloodCenters extends ListRecords
{
    protected static string $resource = BloodCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
