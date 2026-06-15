<?php

namespace App\Filament\Resources\AdminManagement\CohortResource\Pages;

use App\Filament\Resources\AdminManagement\CohortResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCohorts extends ListRecords
{
    protected static string $resource = CohortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
