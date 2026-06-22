<?php

namespace App\Filament\Resources\BloodUnitResource\Pages;

use App\Filament\Resources\BloodUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBloodUnit extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = BloodUnitResource::class;
}
