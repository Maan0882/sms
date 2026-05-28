<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Filament\Admin\Widgets\AdminStatsWidget;
use App\Filament\Admin\Widgets\PendingApplicationsWidget;

class AdminDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Admin Dashboard';
    protected static ?int    $navigationSort  = -1;

    public function getWidgets(): array
    {
        return [
            AdminStatsWidget::class,
            PendingApplicationsWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'sm' => 2, 'lg' => 4];
    }
}
