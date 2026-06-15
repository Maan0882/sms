<?php

namespace App\Filament\Resources\AdminManagement\MentorResource\Pages;

use App\Filament\Resources\AdminManagement\MentorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMentors extends ListRecords
{
    protected static string $resource = MentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New Mentor') // If you explicitly set the label
                ->icon('heroicon-o-user-plus'), // Adds the Heroicon ➕👤,
        ];
    }
}
