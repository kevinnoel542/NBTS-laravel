<?php

namespace App\Filament\Resources\DeferralResource\Pages;

use App\Filament\Resources\DeferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeferral extends ViewRecord
{
    protected static string $resource = DeferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
