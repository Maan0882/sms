<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\AdminDashboard;
use App\Filament\Admin\Resources\ApplicationResource;
use App\Filament\Admin\Resources\CohortResource;
use App\Filament\Admin\Resources\EvaluationResource;
use App\Filament\Admin\Resources\MentorResource;
use App\Filament\Admin\Resources\ProgramResource;
use App\Filament\Admin\Resources\ReportResource;
use App\Filament\Admin\Resources\StudentResource;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ── Identity ───────────────────────────────────────────────
            ->id('admin')
            ->path('admin')
            ->login()

            // ── Branding ───────────────────────────────────────────────
            ->colors(['primary' => Color::Red])
            ->brandName('IAPES · Admin')

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            
            // ── Resources ──────────────────────────────────────────────
            // ->resources([
            //     MentorResource::class,
            //     StudentResource::class,
            //     ProgramResource::class,
            //     CohortResource::class,
            //     ApplicationResource::class,
            //     EvaluationResource::class,
            //     ReportResource::class,
            // ])

            // // ── Pages ──────────────────────────────────────────────────
            // ->pages([
            //     AdminDashboard::class,
            // ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])

            // ── Navigation groups ──────────────────────────────────────
            // ->navigationGroups([
            //     NavigationGroup::make('People')
            //         ->icon('heroicon-o-users')
            //         ->collapsed(false),

            //     NavigationGroup::make('Programs')
            //         ->icon('heroicon-o-academic-cap')
            //         ->collapsed(false),

            //     NavigationGroup::make('Applications')
            //         ->icon('heroicon-o-document-text')
            //         ->collapsed(false),

            //     NavigationGroup::make('Reports')
            //         ->icon('heroicon-o-chart-bar')
            //         ->collapsed(true),
            // ])

            // ── Middleware ─────────────────────────────────────────────
            ->authMiddleware([Authenticate::class])
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

            // ── Gate: admin role only ──────────────────────────────────
            // ->authorizationCallback(function (User $user): bool {
            //     return $user->hasRole('admin') || $user->hasRole('super_admin');
            // });
    }
}
