<?php

namespace App\Filament\Resources\AdminManagement\ApplicationResource\Pages;

use App\Filament\Resources\AdminManagement\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
