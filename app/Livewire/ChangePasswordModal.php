<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;

class ChangePasswordModal extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    #[On('open-change-password-modal')]
    public function openModal(): void
    {
        $this->resetErrorBag();
        $this->form->fill();
        $this->dispatch('open-modal', id: 'change-password-modal');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (filled($value) && ! Hash::check($value, auth()->user()->password)) {
                                    $fail('The current password you entered is incorrect.');
                                }
                            };
                        },
                    ]),

                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->same('password_confirmation'),

                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($state['password']),
        ]);

        $this->dispatch('close-modal', id: 'change-password-modal');

        Notification::make()
            ->title('Password updated successfully')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.change-password-modal');
    }
}
