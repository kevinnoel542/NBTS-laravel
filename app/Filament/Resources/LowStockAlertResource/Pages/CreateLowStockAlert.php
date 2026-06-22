<?php

namespace App\Filament\Resources\LowStockAlertResource\Pages;

use App\Filament\Resources\LowStockAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLowStockAlert extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = LowStockAlertResource::class;
}
