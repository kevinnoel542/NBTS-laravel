<?php

namespace App\Filament\Resources\CenterStaffResource\Pages;

use App\Filament\Resources\CenterStaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCenterStaff extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = CenterStaffResource::class;
}
