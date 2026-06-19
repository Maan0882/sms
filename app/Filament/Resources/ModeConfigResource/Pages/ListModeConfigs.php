<?php

namespace App\Filament\Resources\ModeConfigResource\Pages;

use App\Filament\Resources\ModeConfigResource;
use Filament\Resources\Pages\ListRecords;

class ListModeConfigs extends ListRecords
{
    protected static string $resource = ModeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
