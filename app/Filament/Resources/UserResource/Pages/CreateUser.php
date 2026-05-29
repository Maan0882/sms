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
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
