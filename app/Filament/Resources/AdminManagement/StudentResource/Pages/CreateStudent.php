<?php

namespace App\Filament\Resources\AdminManagement\StudentResource\Pages;

use App\Filament\Resources\AdminManagement\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $institutionId = \Filament\Facades\Filament::getTenant()?->id;

        $user = \App\Models\User::create([
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'institution_id' => $institutionId,
            'is_active' => true,
        ]);
        
        $user->assignRole('student');

        $data['user_id'] = $user->id;
        
        // Remove password from data so it doesn't cause SQL errors
        unset($data['password']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
