<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'All Users' => ListRecords\Tab::make()
                ->icon('heroicon-o-users'),

            'Mentors' => ListRecords\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'mentor')))
                ->icon('heroicon-o-academic-cap'),

            'Students' => ListRecords\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'student')))
                ->icon('heroicon-o-book-open'),

            'Active Users' => ListRecords\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->icon('heroicon-o-user'),

            'Inactive Users' => ListRecords\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->icon('heroicon-o-user-minus'),

            'Deleted Users' => ListRecords\Tab::make()
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New User') // If you explicitly set the label
                ->icon('heroicon-m-user-plus'), // Adds the Heroicon ➕👤
        ];
    }
}
