<?php

namespace App\Filament\Resources\AdminManagement\MentorResource\Pages;

use App\Filament\Resources\AdminManagement\MentorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMentor extends CreateRecord
{
    protected static string $resource = MentorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin') && ! $user->isSuperAdmin()) {
            $data['institution_id'] = $user->institution_id;
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\User $record */
        $record = $this->getRecord();
        $record->assignRole('mentor');

        $nameParts = explode(' ', $record->name, 2);
        
        \App\Models\Mentor::firstOrCreate(
            ['user_id' => $record->id],
            [
                'institution_id' => $record->institution_id,
                'first_name'     => $nameParts[0],
                'last_name'      => $nameParts[1] ?? '',
                'email'          => $record->email,
                'avatar'         => $record->avatar_url,
                'status'         => $record->is_active ? 'active' : 'inactive',
            ]
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
