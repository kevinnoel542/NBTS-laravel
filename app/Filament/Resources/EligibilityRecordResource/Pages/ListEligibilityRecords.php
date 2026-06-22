<?php

namespace App\Filament\Resources\EligibilityRecordResource\Pages;

use App\Filament\Resources\EligibilityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEligibilityRecords extends ListRecords
{
    protected static string $resource = EligibilityRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
