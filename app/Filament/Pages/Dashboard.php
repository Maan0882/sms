<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\AuditLogWidget;
use App\Filament\Widgets\RecentUsersWidget;
use App\Filament\Widgets\RoleDistributionWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\AdminStatsWidget;
// If MentorOverviewWidget does not exist, we should probably comment it out or leave it if it does.
use App\Filament\Widgets\MentorOverviewWidget;

class Dashboard extends BaseDashboard
{
    // 1. Dynamically change the heading title based on role
    public function getTitle(): string
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return 'Superadmin Command Center';
        }
        if ($user->hasRole('admin')) {
            return 'Administrative Overview';
        }
        return 'Mentor Workspace';
    }

    // 2. Dynamically load entirely different widgets per role
    public function getWidgets(): array
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole('superadmin') => [
                AuditLogWidget::class,
                RecentUsersWidget::class,
                RoleDistributionWidget::class,
                StatsOverviewWidget::class,
            ],
            $user->hasRole('admin') => [
                AdminStatsWidget::class,
            ],
            $user->hasRole('mentor') => [
                // MentorOverviewWidget::class,
            ],
            default => [],
        };
    }
}