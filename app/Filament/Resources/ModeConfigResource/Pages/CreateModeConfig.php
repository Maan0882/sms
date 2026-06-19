<?php

namespace App\Filament\Resources\ModeConfigResource\Pages;

use App\Filament\Resources\ModeConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateModeConfig extends CreateRecord
{
    protected static string $resource = ModeConfigResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
