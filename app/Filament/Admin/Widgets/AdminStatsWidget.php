<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Mentors', User::role('mentor')->count())
                ->description(User::role('mentor')->where('is_active', true)->count() . ' active')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Total Students', User::role('student')->count())
                ->description(User::role('student')->where('is_active', true)->count() . ' active')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),

            Stat::make('Active Programs', Program::where('is_active', true)->count())
                ->description(Program::count() . ' total programs')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),

            Stat::make('Pending Applications', Application::where('status', 'pending')->count())
                ->description('Awaiting your review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Approved', Application::where('status', 'approved')->count())
                ->description('Applications approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Rejected', Application::where('status', 'rejected')->count())
                ->description('Applications rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'),
        ];
    }
}
