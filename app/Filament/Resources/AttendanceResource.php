<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Academic Management'; // Kept 'Academic Management' instead of 'Intern' for consistency with project
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin', 'mentor']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Student')
                    ->relationship('student', 'first_name', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();
                        if ($user && $user->hasRole(['admin', 'mentor']) && !$user->hasRole('super_admin')) {
                            $query->where('institution_id', $user->institution_id);
                        }
                        $query->where('enrollment_status', 'enrolled');
                        return $query;
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'leave' => 'Leave',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('remarks')
                    ->label('Remarks'),

                // Hidden fields for multi-tenancy and tracking
                Forms\Components\Hidden::make('institution_id')
                    ->default(fn () => auth()->user()->institution_id),

                Forms\Components\Hidden::make('mentor_id')
                    ->default(fn () => auth()->id()),
                
                // Keeping cohort_id and program_id in the form in case we still need them
                Forms\Components\Hidden::make('cohort_id'),
                Forms\Components\Hidden::make('program_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('student.student_id')
                    ->label('Student ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('student.full_name')
                    ->label('Student Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('semibold'),

                // 1. BETTER UI: Mark status directly in the table row
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'leave' => 'Leave',
                    ])
                    ->selectablePlaceholder(false)
                    ->afterStateUpdated(fn ($record, $state) => 
                        $record->update(['status' => $state])
                    ),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('mentor.name')
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // 2. THE GROUPING UI: Creates headers grouped by Cohort
            ->groups([
                Group::make('student.cohort.name')
                    ->label('Cohort')
                    ->getTitleFromRecordUsing(function ($record): string {
                        $cohort = $record->student?->cohort;
                        $program = $record->student?->program;
                        
                        $cohortName = $cohort?->name ?? 'Unknown Cohort';
                        $programName = $program?->name ?? 'No Program';

                        return "{$cohortName} | Program: {$programName}";
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('student.cohort.name')
            ->filters([
                // Automatically focus on today's attendance records
                Tables\Filters\Filter::make('today')
                    ->label('Today Only')
                    ->default()
                    ->query(fn ($query) => $query->whereDate('date', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markPresent')
                        ->label('Mark Selected Present')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'present'])),
                    
                    Tables\Actions\BulkAction::make('markAbsent')
                        ->label('Mark Selected Absent')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'absent'])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return true; // Enabling creation because mentors will need to log new attendances.
    }

    public static function canViewAny(): bool
    {
        return true; 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();

        // Access control by institution
        if ($user && $user->hasRole('admin') && ! $user->hasRole('super_admin')) {
            $query->where('institution_id', $user->institution_id);
        } elseif ($user && $user->hasRole('mentor')) {
            $query->where('institution_id', $user->institution_id)
                  ->where('mentor_id', $user->id);
        }

        // Only show attendance for currently enrolled students
        $query->whereHas('student', function (Builder $q) {
            $q->where('enrollment_status', 'enrolled');
        });

        return $query;
    }
}
