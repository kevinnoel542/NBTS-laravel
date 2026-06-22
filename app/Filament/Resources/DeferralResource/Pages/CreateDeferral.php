<?php

namespace App\Filament\Resources\DeferralResource\Pages;

use App\Filament\Resources\DeferralResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeferral extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = DeferralResource::class;
}
