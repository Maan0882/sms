<?php

namespace App\Filament\Widgets;

use App\Filament\Admin\Resources\ApplicationResource;
use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingApplicationsWidget extends BaseWidget
{
    protected static ?int    $sort    = 2;
    protected static ?string $heading = 'Pending Applications';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only Superadmins will ever see this widget on the unified dashboard
        return auth()->user()->hasRole('admin');
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Application::query()
                    ->with(['student', 'program', 'cohort'])
                    ->where('status', 'pending')
                    ->latest()
                    ->limit(10);
                
                if (auth()->check() && auth()->user()->hasRole('admin') && auth()->user()->institution_id) {
                    $query->where('institution_id', auth()->user()->institution_id);
                }
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('cohort.name')
                    ->label('Cohort')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->since()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('Review')
                    ->url(fn (Application $record) =>
                        ApplicationResource::getUrl('edit', ['record' => $record])
                    )
                    ->icon('heroicon-m-eye')
                    ->color('info'),
            ])
            ->paginated(false);
    }
}
