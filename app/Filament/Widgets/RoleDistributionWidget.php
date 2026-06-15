<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class RoleDistributionWidget extends ChartWidget
{
    protected static ?int    $sort    = 2;
    protected static ?string $heading = 'Users by Role';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        // Only Superadmins will ever see this widget on the unified dashboard
        return auth()->user()->hasRole('super_admin');
    }

    protected function getData(): array
    {
        $roles = [
            'Super Admin' => User::role('super_admin')->count(),
            'Admin'       => User::role('admin')->count(),
            'Mentor'      => User::role('mentor')->count(),
            'Student'     => User::role('student')->count(),
            'No Role'     => User::doesntHave('roles')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Users',
                    'data'            => array_values($roles),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#ef4444',
                        '#22c55e',
                        '#3b82f6',
                        '#9ca3af',
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => array_keys($roles),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'cutout' => '65%',
        ];
    }
}
