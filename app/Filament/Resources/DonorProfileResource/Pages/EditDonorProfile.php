<?php

namespace App\Filament\Resources\DonorProfileResource\Pages;

use App\Filament\Resources\DonorProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonorProfile extends EditRecord
{
    protected static string $resource = DonorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
