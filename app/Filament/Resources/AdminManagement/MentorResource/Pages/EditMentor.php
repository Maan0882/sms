<?php

namespace App\Filament\Resources\AdminManagement\MentorResource\Pages;

use App\Filament\Resources\AdminManagement\MentorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMentor extends EditRecord
{
    protected static string $resource = MentorResource::class;

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
