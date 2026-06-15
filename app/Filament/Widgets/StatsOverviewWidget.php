<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only Superadmins will ever see this widget on the unified dashboard
        return auth()->user()->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            return User::whereDate('created_at', now()->subDays($daysAgo))->count();
        })->toArray();

        return [
            Stat::make('Total Users', number_format(User::count()))
                ->description(User::whereDate('created_at', today())->count() . ' joined today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($last7Days)
                ->color('primary'),

            Stat::make('Active Users', number_format(User::where('is_active', true)->count()))
                ->description(
                    round((User::where('is_active', true)->count() / max(User::count(), 1)) * 100) . '% of total users'
                )
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Inactive Users', number_format(User::where('is_active', false)->count()))
                ->description('Accounts currently deactivated')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('In Trash', number_format(User::onlyTrashed()->count()))
                ->description('Soft deleted — restorable')
                ->descriptionIcon('heroicon-m-trash')
                ->color('warning'),

            Stat::make('Roles', Role::count())
                ->description(Permission::count() . ' permissions total')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),

            Stat::make('Admins', User::role('admin')->count())
                ->description(
                    User::role('mentor')->count() . ' mentors · ' .
                    User::role('student')->count() . ' students'
                )
                ->descriptionIcon('heroicon-m-building-library')
                ->color('gray'),
        ];
    }
}
