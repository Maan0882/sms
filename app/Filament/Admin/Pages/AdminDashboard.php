<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard;
use App\Filament\Admin\Widgets\AdminStatsWidget;
use App\Filament\Admin\Widgets\PendingApplicationsWidget;
use App\Models\Application;
use App\Models\Program;
use App\Models\User;

class AdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Dashboard';
    protected static ?int    $navigationSort  = -1;
 
    protected static string $view = 'filament.admin.pages.admin-dashboard';
 
    public function getWidgets(): array
    {
        return [
            AdminStatsWidget::class,
            PendingApplicationsWidget::class,
        ];
    }
 
    public function getColumns(): int | string | array
    {
        return 1;
    }
 
    public function getDashboardData(): array
    {
        $totalMentors      = User::role('mentor')->count();
        $activeMentors     = User::role('mentor')->where('is_active', true)->count();
        $totalStudents     = User::role('student')->count();
        $activeStudents    = User::role('student')->where('is_active', true)->count();
        $activePrograms    = Program::where('is_active', true)->count();
        $totalPrograms     = Program::count();
        $pendingApps       = Application::where('status', 'pending')->count();
        $approvedApps      = Application::where('status', 'approved')->count();
        $rejectedApps      = Application::where('status', 'rejected')->count();
        $totalApps         = Application::count();
        $newStudentsMonth  = User::role('student')->whereMonth('created_at', now()->month)->count();
        $newMentorsMonth   = User::role('mentor')->whereMonth('created_at', now()->month)->count();
 
        // Application trend last 6 months
        $trend = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'label'    => $month->format('M'),
                'pending'  => Application::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->where('status', 'pending')->count(),
                'approved' => Application::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->where('status', 'approved')->count(),
            ];
        });
 
        return compact(
            'totalMentors', 'activeMentors', 'totalStudents', 'activeStudents',
            'activePrograms', 'totalPrograms', 'pendingApps', 'approvedApps',
            'rejectedApps', 'totalApps', 'newStudentsMonth', 'newMentorsMonth', 'trend'
        );
    }
}
