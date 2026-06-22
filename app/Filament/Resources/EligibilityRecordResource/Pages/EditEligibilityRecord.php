<?php

namespace App\Filament\Resources\EligibilityRecordResource\Pages;

use App\Filament\Resources\EligibilityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEligibilityRecord extends EditRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = EligibilityRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
