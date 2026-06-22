<?php

namespace App\Filament\Resources\BloodCenterResource\Pages;

use App\Filament\Resources\BloodCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBloodCenter extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = BloodCenterResource::class;
}
