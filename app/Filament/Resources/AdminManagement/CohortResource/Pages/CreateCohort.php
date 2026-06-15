<?php

namespace App\Filament\Resources\AdminManagement\CohortResource\Pages;

use App\Filament\Resources\AdminManagement\CohortResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCohort extends CreateRecord
{
    protected static string $resource = CohortResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
