<?php

namespace App\Filament\Admin\Resources\MentorResource\Pages;

use App\Filament\Admin\Resources\MentorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMentor extends CreateRecord
{
    protected static string $resource = MentorResource::class;

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
