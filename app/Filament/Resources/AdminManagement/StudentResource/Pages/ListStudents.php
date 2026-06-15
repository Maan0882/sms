<?php

namespace App\Filament\Resources\AdminManagement\StudentResource\Pages;

use App\Filament\Resources\AdminManagement\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New Student') // If you explicitly set the label
                ->icon('heroicon-o-user-plus'), // Adds the Heroicon ➕👤,
        ];
    }
}
