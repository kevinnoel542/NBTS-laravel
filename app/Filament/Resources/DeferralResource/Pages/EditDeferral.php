<?php

namespace App\Filament\Resources\DeferralResource\Pages;

use App\Filament\Resources\DeferralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeferral extends EditRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = DeferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
