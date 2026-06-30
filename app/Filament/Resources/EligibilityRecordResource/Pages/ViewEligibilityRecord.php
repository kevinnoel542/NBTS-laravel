<?php

namespace App\Filament\Resources\EligibilityRecordResource\Pages;

use App\Filament\Resources\EligibilityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEligibilityRecord extends ViewRecord
{
    protected static string $resource = EligibilityRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
