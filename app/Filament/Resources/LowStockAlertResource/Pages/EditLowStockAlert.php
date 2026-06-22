<?php

namespace App\Filament\Resources\LowStockAlertResource\Pages;

use App\Filament\Resources\LowStockAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLowStockAlert extends EditRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = LowStockAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
