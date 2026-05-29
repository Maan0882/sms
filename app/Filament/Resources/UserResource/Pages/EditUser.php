<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public array $oldRoles = [];

    protected function beforeSave(): void
    {
        $this->oldRoles = $this->record->roles->pluck('name')->toArray();
    }

    protected function afterSave(): void
    {
        $newRoles = $this->record->roles()->pluck('name')->toArray();
        
        if ($this->oldRoles !== $newRoles) {
            \App\Models\Audit::create([
                'auditable_type' => \App\Models\User::class,
                'auditable_id'   => $this->record->id,
                'event'          => 'updated',
                'old_values'     => ['roles' => $this->oldRoles],
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
