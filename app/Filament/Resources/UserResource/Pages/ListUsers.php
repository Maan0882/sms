<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New User') // If you explicitly set the label
                ->icon('heroicon-m-user-plus'), // Adds the Heroicon ➕👤
        ];
    }

    public function getTabs(): array
    {
        return [
            'All Users' => ListRecords\Tab::make()
                ->icon('heroicon-o-user-group')
                ->badge(User::count()),

            'Admins' => ListRecords\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'admin')))
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
}
