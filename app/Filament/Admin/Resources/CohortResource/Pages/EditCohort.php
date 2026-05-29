<?php

namespace App\Filament\Admin\Resources\CohortResource\Pages;

use App\Filament\Admin\Resources\CohortResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCohort extends EditRecord
{
    protected static string $resource = CohortResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
