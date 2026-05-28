<?php

namespace App\Filament\Admin\Resources\CohortResource\Pages;

use App\Filament\Admin\Resources\CohortResource;
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
