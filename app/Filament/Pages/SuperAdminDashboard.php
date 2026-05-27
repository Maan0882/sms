<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AuditLogWidget;
use App\Filament\Widgets\RecentUsersWidget;
use App\Filament\Widgets\RoleDistributionWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard;

class SuperAdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Super Admin Dashboard';
    protected static ?int    $navigationSort  = -1;

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RoleDistributionWidget::class,
            RecentUsersWidget::class,
            AuditLogWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 4,
        ];
    }
}
