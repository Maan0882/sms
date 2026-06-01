<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SuperAdminDashboard;
use App\Filament\Pages\ManageRolesPermissions;
use App\Filament\Pages\ManageBackups;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ── Identity ───────────────────────────────────────────────
            ->id('superAdmin')
            ->path('superAdmin')
            ->login()
            ->authGuard('web')
            // ── Branding ───────────────────────────────────────────────
            ->colors(['primary' => Color::Amber])
            ->brandName('IAPES · SuperAdmin')

            // ── Pages & Resources ──────────────────────────────────────
            ->pages([
                SuperAdminDashboard::class,
            ])
            ->resources([
                UserResource::class,
                RoleResource::class,
                PermissionResource::class,
                AuditLogResource::class,
                \App\Filament\Resources\InstitutionResource::class,
            ])

            // ── Sidebar navigation groups ──────────────────────────────
            ->navigationGroups([
                NavigationGroup::make('Institute Management')
                    ->collapsed(false), // You can set this to false if you want it expanded by default
                NavigationGroup::make('User Management')
                    ->collapsed(false),
                NavigationGroup::make('Access Control')
                    ->collapsed(false),
                NavigationGroup::make('System')
                    ->collapsed(true),
            ])

            // ── Widgets ────────────────────────────────────────────────
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])

            // ── Middleware ─────────────────────────────────────────────
            ->authMiddleware([
                Authenticate::class,
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
            ]);

            // ── Gate: super_admin role only ────────────────────────────
            // ->auth(function (User $user): bool {
            //     return $user->hasRole('super_admin');
            // });
    }
}