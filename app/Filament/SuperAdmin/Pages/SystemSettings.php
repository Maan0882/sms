<?php

namespace App\Filament\SuperAdmin\Pages;
use Filament\Forms\Form; // <-- Add this line
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Page
{
    use InteractsWithForms;

    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.super-admin.pages.system-settings'; //  Fixed

    public static function getNavigationIcon(): string { return 'heroicon-o-cog-6-tooth'; }
    public static function getNavigationGroup(): ?string { return 'System'; }
    public static function getNavigationLabel(): string { return 'System Settings'; }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name'          => config('app.name'),
            'app_timezone'      => config('app.timezone'),
            'app_locale'        => config('app.locale'),
            'maintenance_mode'  => app()->isDownForMaintenance(),
            'mail_driver'       => config('mail.default'),
            'mail_from_name'    => config('mail.from.name'),
            'mail_from_address' => config('mail.from.address'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->components([
            Section::make('Application Settings')
                ->icon('heroicon-o-globe-alt')
                ->schema([
                    TextInput::make('app_name')
                        ->label('Application Name')
                        ->required(),
                    Select::make('app_timezone')
                        ->label('Timezone')
                        ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray())
                        ->searchable(),
                    Select::make('app_locale')
                        ->label('Default Locale')
                        ->options(['en' => 'English', 'fr' => 'French', 'de' => 'German', 'es' => 'Spanish', 'ar' => 'Arabic']),
                    Toggle::make('maintenance_mode')
                        ->label('Maintenance Mode')
                        ->helperText('Puts the application into maintenance mode'),
                ])->columns(2),

            \Filament\Forms\Components\Section::make('Mail Settings')
                ->icon('heroicon-o-envelope')
                ->schema([
                    Select::make('mail_driver')
                        ->label('Mail Driver')
                        ->options(['smtp' => 'SMTP', 'mailgun' => 'Mailgun', 'ses' => 'AWS SES', 'log' => 'Log (Dev)']),
                    TextInput::make('mail_from_name')->label('From Name'),
                    TextInput::make('mail_from_address')->label('From Address')->email(),
                ])->columns(2),

            \Filament\Forms\Components\Section::make('Cache & Performance')
                ->icon('heroicon-o-bolt')
                ->schema([
                    Toggle::make('cache_enabled')->label('Enable Cache')->default(true),
                    Toggle::make('query_cache')->label('Enable Query Cache')->default(false),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Cache::put('system_settings', $data, now()->addYear());

        if ($data['maintenance_mode'] ?? false) {
            Artisan::call('down');
        } else {
            Artisan::call('up');
        }

        Notification::make()->title('Settings saved successfully')->success()->send();
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Notification::make()->title('All caches cleared successfully')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
