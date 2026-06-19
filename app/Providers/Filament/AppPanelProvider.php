<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('')
            // ->topNavigation()
            ->brandName('IMS')
            ->brandLogo(asset('/images/TsLogo.png'))
            ->brandLogoHeight('3.2rem')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->profile(\App\Filament\Pages\Auth\CustomEditProfile::class)
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label('Change Password')
                    ->url('javascript:Livewire.dispatch(\'open-change-password-modal\')')
                    ->icon('heroicon-o-key'),
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '
                    <style>
                        /* Glassmorphism for Simple Layout Cards (Profile, Login, etc.) */
                        .fi-simple-main {
                            background: rgba(24, 24, 27, 0.4) !important;
                            backdrop-filter: blur(16px) saturate(180%) !important;
                            -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
                            border: 1px solid rgba(255, 255, 255, 0.08) !important;
                            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4) !important;
                            border-radius: 1rem !important;
                        }
                        .fi-simple-layout {
                            background: radial-gradient(circle at center, #18181b 0%, #09090b 100%) !important;
                        }
                        /* Glassmorphism & Blur backdrop for modals */
                        .fi-modal-close-overlay {
                            backdrop-filter: blur(8px) !important;
                            -webkit-backdrop-filter: blur(8px) !important;
                            background: rgba(0, 0, 0, 0.5) !important;
                        }
                    </style>
                ',
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => \Illuminate\Support\Facades\Blade::render("@livewire('change-password-modal')"),
            )
            ->navigationGroups([
                NavigationGroup::make('Institute Management')
                    ->collapsed(false),
                NavigationGroup::make('Applications')
                    ->collapsed(false),
                NavigationGroup::make('User Management')
                    ->collapsed(false),
                NavigationGroup::make('Access Control')
                    ->collapsed(false),
                NavigationGroup::make('System')
                    ->collapsed(true),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
