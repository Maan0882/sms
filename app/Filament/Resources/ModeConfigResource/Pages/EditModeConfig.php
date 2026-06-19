<?php

namespace App\Filament\Resources\ModeConfigResource\Pages;

use App\Filament\Resources\ModeConfigResource;
use Filament\Resources\Pages\EditRecord;

class EditModeConfig extends EditRecord
{
    protected static string $resource = ModeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
