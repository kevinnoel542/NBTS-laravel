<?php

namespace App\Filament\Resources\EligibilityRecordResource\Pages;

use App\Filament\Resources\EligibilityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEligibilityRecord extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = EligibilityRecordResource::class;
}
