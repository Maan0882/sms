<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
     protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
 
    // Poll every 30s for live feel
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        // Only Superadmins will ever see this widget on the unified dashboard
        return auth()->user()->hasRole('admin');
    }
 
    protected function getStats(): array
    {
        $user = auth()->user();
        $institutionId = ($user && $user->hasRole('admin')) ? $user->institution_id : null;

        $mentorQuery = User::role('mentor');
        $studentQuery = User::role('student');
        $programQuery = Program::query();
        $applicationQuery = Application::query();

        if ($institutionId) {
            $mentorQuery->where('institution_id', $institutionId);
            $studentQuery->where('institution_id', $institutionId);
            $programQuery->where('institution_id', $institutionId);
            $applicationQuery->where('institution_id', $institutionId);
        }

        $mentorTotal   = (clone $mentorQuery)->count();
        $mentorActive  = (clone $mentorQuery)->where('is_active', true)->count();
        $studentTotal  = (clone $studentQuery)->count();
        $studentActive = (clone $studentQuery)->where('is_active', true)->count();
        $programActive = (clone $programQuery)->where('is_active', true)->count();
        $programTotal  = (clone $programQuery)->count();
        $pending       = (clone $applicationQuery)->where('status', 'pending')->count();
        $approved      = (clone $applicationQuery)->where('status', 'approved')->count();
        $rejected      = (clone $applicationQuery)->where('status', 'rejected')->count();
 
        // Sparkline-style trend: new students last 7 days
        $studentTrend = collect(range(6, 0))
            ->map(fn ($i) => (clone $studentQuery)->whereDate('created_at', now()->subDays($i))->count())
            ->toArray();
 
        $appTrend = collect(range(6, 0))
            ->map(fn ($i) => (clone $applicationQuery)->whereDate('created_at', now()->subDays($i))->count())
            ->toArray();
 
        return [
            Stat::make('Total Mentors', $mentorTotal)
                ->description($mentorActive . ' active · ' . ($mentorTotal - $mentorActive) . ' inactive')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart(collect(range(6, 0))->map(fn ($i) => (clone $mentorQuery)->whereDate('created_at', now()->subDays($i))->count())->toArray()),
 
            Stat::make('Total Students', $studentTotal)
                ->description($studentActive . ' active · ' . number_format($studentTotal > 0 ? $studentActive / $studentTotal * 100 : 0, 0) . '% active rate')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart($studentTrend),
 
            Stat::make('Active Programs', $programActive)
                ->description($programTotal . ' total · ' . ($programTotal - $programActive) . ' inactive')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
 
            Stat::make('Pending Applications', $pending)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'danger' : 'success')
                ->chart($appTrend),
 
            Stat::make('Approved', $approved)
                ->description(($approved + $rejected) > 0
                    ? number_format($approved / ($approved + $rejected) * 100, 0) . '% approval rate'
                    : 'No decisions yet')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
 
            Stat::make('Rejected', $rejected)
                ->description('Applications rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'),
        ];
    }
}
