<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;

class CustomEditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                
                TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->revealable()
                    ->required(fn (\Filament\Forms\Get $get) => filled($get('password')))
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (filled($value) && ! Hash::check($value, auth()->user()->password)) {
                                    $fail('The current password you entered is incorrect.');
                                }
                            };
                        },
                    ])
                    ->dehydrated(false), // Do not save current password to DB

                $this->getPasswordFormComponent()
                    ->label('New Password')
                    ->required(fn (\Filament\Forms\Get $get) => filled($get('current_password'))),

                $this->getPasswordConfirmationFormComponent()
                    ->label('Confirm Password')
                    ->required(fn (\Filament\Forms\Get $get) => filled($get('password'))),
            ]);
    }
}
