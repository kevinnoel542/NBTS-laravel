<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = RoleResource::class;
}
