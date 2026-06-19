<?php

namespace App\Filament\Pages;

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
    protected static ?int    $navigationSort  = 7;
    protected static ?string $slug            = 'reports-analytics';
    protected static string  $view            = 'filament.admin.pages.admin-reports';

    public static function canAccess(): bool
    {
        // Only SuperAdmins can access this page.
        return auth()->user()->hasRole(['super_admin','admin']);
    }

    public function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');
        $institutionId = $user->institution_id;

        $userQuery = User::query();
        $programQuery = Program::query();
        $applicationQuery = Application::query();
        $evaluationQuery = Evaluation::query();

        if (!$isSuperAdmin && $institutionId) {
            $userQuery->where('institution_id', $institutionId);
            $programQuery->where('institution_id', $institutionId);
            $applicationQuery->where('institution_id', $institutionId);
            $evaluationQuery->where('institution_id', $institutionId);
        }

        $stats = [
            'total_students'      => (clone $userQuery)->role('student')->count(),
            'total_mentors'       => (clone $userQuery)->role('mentor')->count(),
            'total_programs'      => (clone $programQuery)->where('is_active', true)->count(),
            'total_applications'  => (clone $applicationQuery)->count(),
            'pending_apps'        => (clone $applicationQuery)->where('status', 'pending')->count(),
            'approved_apps'       => (clone $applicationQuery)->where('status', 'approved')->count(),
            'rejected_apps'       => (clone $applicationQuery)->where('status', 'rejected')->count(),
            'total_evaluations'   => (clone $evaluationQuery)->count(),
            'avg_score'           => round((clone $evaluationQuery)->avg('score'), 2) ?? 0,
            'pass_rate'           => $this->getPassRate($evaluationQuery),
        ];

        if ($isSuperAdmin) {
            $stats['institutes'] = User::role('student')->whereNotNull('institution_id')->distinct('institution_id')->count('institution_id');
        }

        return $stats;
    }

    private function getPassRate($evaluationQuery = null): float
    {
        $evaluationQuery = $evaluationQuery ?: Evaluation::query();
        $total  = (clone $evaluationQuery)->whereNotNull('grade')->count();
        $passed = (clone $evaluationQuery)->whereNotIn('grade', ['D', 'F'])->count();
        return $total > 0 ? round(($passed / $total) * 100, 1) : 0;
    }

    public function getApplicationsByProgram(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        $query = Program::withCount('applications');
        if (!$isSuperAdmin && $user->institution_id) {
            $query->where('institution_id', $user->institution_id);
        }

        return $query
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
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        $query = Evaluation::selectRaw('grade, COUNT(*) as count')
            ->whereNotNull('grade');
            
        if (!$isSuperAdmin && $user->institution_id) {
            $query->where('institution_id', $user->institution_id);
        }

        return $query->groupBy('grade')
            ->orderBy('grade')
            ->pluck('count', 'grade')
            ->toArray();
    }
}
