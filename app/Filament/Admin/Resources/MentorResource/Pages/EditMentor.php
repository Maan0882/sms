<?php

namespace App\Filament\Admin\Resources\MentorResource\Pages;

use App\Filament\Admin\Resources\MentorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMentor extends EditRecord
{
    protected static string $resource = MentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
