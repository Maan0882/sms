<?php

namespace App\Filament\Resources\AdminManagement\ProgramResource\Pages;

use App\Filament\Resources\AdminManagement\ProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
