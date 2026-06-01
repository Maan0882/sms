<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $newRoles = $this->record->roles()->pluck('name')->toArray();
        if (!empty($newRoles)) {
            \App\Models\Audit::create([
                'auditable_type' => \App\Models\User::class,
                'auditable_id'   => $this->record->id,
                'event'          => 'updated',
                'old_values'     => ['roles' => []],
                'new_values'     => ['roles' => $newRoles],
                'user_id'        => auth()->id(),
                'url'            => request()->fullUrl(),
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
            ]);
        }

        if (in_array('mentor', $newRoles)) {
            $nameParts = explode(' ', $this->record->name, 2);
            
            \App\Models\Mentor::firstOrCreate(
                ['user_id' => $this->record->id],
                [
                    'institution_id' => $this->record->institution_id,
                    'first_name'     => $nameParts[0],
                    'last_name'      => $nameParts[1] ?? '',
                    'email'          => $this->record->email,
                    'avatar'         => $this->record->avatar_url,
                    'status'         => $this->record->is_active ? 'active' : 'inactive',
                ]
            );
        }

        if (in_array('student', $newRoles)) {
            $nameParts = explode(' ', $this->record->name, 2);
            $program = \App\Models\Program::where('institution_id', $this->record->institution_id)->first() ?? \App\Models\Program::first();
            
            if ($program) {
                \App\Models\Student::firstOrCreate(
                    ['user_id' => $this->record->id],
                    [
                        'institution_id'    => $this->record->institution_id,
                        'first_name'        => $nameParts[0],
                        'last_name'         => $nameParts[1] ?? '',
                        'email'             => $this->record->email,
                        'avatar'            => $this->record->avatar_url,
                        'enrollment_status' => $this->record->is_active ? 'enrolled' : 'suspended',
                        'program_id'        => $program->id,
                        'enrollment_date'   => now(),
                    ]
                );
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
