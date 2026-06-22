<?php

namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use App\Services\DonationRecordingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDonation extends CreateRecord
{
    use \App\Filament\Resources\Concerns\RedirectsToResourceIndex;

    protected static string $resource = DonationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if (($data['donation_type'] ?? null) === 'walk_in') {
            $data['appointment_id'] = null;
        }

        return app(DonationRecordingService::class)->record($data, auth()->user());
    }
}
