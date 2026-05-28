<?php

namespace App\Filament\Admin\Pages;

use App\Models\Application;
use App\Models\Evaluation;
use App\Models\Program;
use App\Models\User;
use Filament\Pages\Page;

class AdminReports extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Reports & Analytics';
    protected static ?string $title           = 'Reports & Analytics';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.admin.pages.admin-reports';

    public function getStats(): array
    {
        return [
            'total_students'      => User::role('student')->count(),
            'total_mentors'       => User::role('mentor')->count(),
            'total_programs'      => Program::where('is_active', true)->count(),
            'total_applications'  => Application::count(),
            'pending_apps'        => Application::where('status', 'pending')->count(),
            'approved_apps'       => Application::where('status', 'approved')->count(),
            'rejected_apps'       => Application::where('status', 'rejected')->count(),
            'total_evaluations'   => Evaluation::count(),
            'avg_score'           => round(Evaluation::avg('score'), 2) ?? 0,
            'pass_rate'           => $this->getPassRate(),
        ];
    }

    private function getPassRate(): float
    {
        $total  = Evaluation::whereNotNull('grade')->count();
        $passed = Evaluation::whereNotIn('grade', ['D', 'F'])->count();
        return $total > 0 ? round(($passed / $total) * 100, 1) : 0;
    }

    public function getApplicationsByProgram(): array
    {
        return Program::withCount('applications')
            ->having('applications_count', '>', 0)
            ->get()
            ->map(fn ($p) => [
                'name'  => $p->name,
                'count' => $p->applications_count,
            ])
            ->toArray();
    }

    public function getGradeDistribution(): array
    {
        return Evaluation::selectRaw('grade, COUNT(*) as count')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->orderBy('grade')
            ->pluck('count', 'grade')
            ->toArray();
    }
}
