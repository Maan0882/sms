<?php

namespace App\Filament\Resources\AdminManagement;

use App\Filament\Resources\AdminManagement\EvaluationResource\Pages;
use App\Models\Cohort;
use App\Models\Evaluation;
use App\Models\Program;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluationResource extends Resource
{
    protected static ?string $model           = Evaluation::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Applications';
    protected static ?string $navigationLabel = 'Evaluations';
    protected static ?int    $navigationSort  = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }
    
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Evaluation Details')
                ->schema([
                    Forms\Components\Select::make('student_id')
                        ->label('Student')
                        ->options(User::role('student')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('mentor_id')
                        ->label('Mentor')
                        ->options(User::role('mentor')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('program_id')
                        ->label('Program')
                        ->options(Program::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('cohort_id', null)),

                    Forms\Components\Select::make('cohort_id')
                        ->label('Cohort')
                        ->options(fn ($get) =>
                            Cohort::where('program_id', $get('program_id'))
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->nullable(),

                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->placeholder('e.g. Mid-Term Evaluation')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Scores')
                ->schema([
                    Forms\Components\TextInput::make('score')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('e.g. 87.50'),

                    Forms\Components\TextInput::make('max_score')
                        ->numeric()
                        ->default(100)
                        ->minValue(1),

                    Forms\Components\Select::make('grade')
                        ->options([
                            'A+' => 'A+ (Excellent)',
                            'A'  => 'A  (Very Good)',
                            'B'  => 'B  (Good)',
                            'C'  => 'C  (Average)',
                            'D'  => 'D  (Below Average)',
                            'F'  => 'F  (Fail)',
                        ]),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'submitted' => 'Submitted',
                            'reviewed'  => 'Reviewed',
                        ])
                        ->default('pending'),

                    Forms\Components\Textarea::make('feedback')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('evaluated_at')
                        ->label('Evaluated At'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->weight('semibold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mentor.name')
                    ->label('Mentor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('title')
                    ->limit(30)
                    ->tooltip(fn (Evaluation $record) => $record->title),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn (Evaluation $record) =>
                        $record->score . ' / ' . $record->max_score .
                        ' (' . $record->percentage . '%)'
                    ),

                Tables\Columns\TextColumn::make('grade')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'A+', 'A' => 'success',
                        'B'       => 'info',
                        'C'       => 'warning',
                        'D', 'F'  => 'danger',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed'  => 'success',
                        'submitted' => 'info',
                        'pending'   => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('evaluated_at')
                    ->label('Evaluated')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'submitted' => 'Submitted',
                        'reviewed'  => 'Reviewed',
                    ]),
                Tables\Filters\SelectFilter::make('grade')
                    ->options(['A+' => 'A+', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'F' => 'F']),
                Tables\Filters\SelectFilter::make('program')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\DeleteAction::make()->label('Move to Trash'),
                Tables\Actions\ForceDeleteAction::make()->requiresConfirmation(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('evaluated_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['student', 'mentor', 'program', 'cohort'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        if (auth()->check() && auth()->user()->hasRole('admin') && auth()->user()->institution_id) {
            $query->where('institution_id', auth()->user()->institution_id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluations::route('/'),
            'create' => Pages\CreateEvaluation::route('/create'),
            'edit' => Pages\EditEvaluation::route('/{record}/edit'),
        ];
    }
}
