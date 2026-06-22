<?php

namespace App\Filament\Resources\CenterStaffResource\Pages;

use App\Filament\Resources\CenterStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCenterStaff extends EditRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = CenterStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
