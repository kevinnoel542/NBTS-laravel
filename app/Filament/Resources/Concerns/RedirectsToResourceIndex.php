<?php

namespace App\Filament\Resources\Concerns;

trait RedirectsToResourceIndex
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
