<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Checkbox::make('logout_all')
                    ->label('Log out of all sessions after saving')
                    ->helperText('Check this to log out completely and be redirected to the login page.')
                    ->default(false)
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    protected function afterSave(): void
    {
        if (isset($this->data['logout_all']) && $this->data['logout_all']) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirect(filament()->getLoginUrl());
        }
    }
}
