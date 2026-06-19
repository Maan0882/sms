<?php

namespace App\Filament\Resources\AdminManagement;

use App\Filament\Resources\AdminManagement\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramResource extends Resource
{
    protected static ?string $model           = Program::class;
    protected static ?string $navigationIcon  = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Programs';
    protected static ?string $navigationLabel = 'Programs';
    protected static ?string $slug            = 'programs';
    protected static ?int    $navigationSort  = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'admin']) || auth()->user()->hasPermissionTo('program.view');
    }
    
    // ── FORM ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Program Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Bachelor of Computer Applications'),

                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(Program::class, 'code', ignoreRecord: true)
                        ->placeholder('e.g. BCA2024')
                        ->helperText('Unique program code'),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('duration_months')
                        ->label('Duration (Months)')
                        ->numeric()
                        ->required()
                        ->minValue(1),

                    Forms\Components\TextInput::make('max_students')
                        ->label('Max Students')
                        ->numeric()
                        ->required()
                        ->default(50),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active Program')
                        ->default(true),
                ])
                ->columns(2),

            Forms\Components\Section::make('Schedule')
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->after('start_date'),
                ])
                ->columns(2),
        ]);
    }

    // ── TABLE ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state . ' months')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('cohorts_count')
                    ->label('Cohorts')
                    ->counts('cohorts')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Starts')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Ends')
                    ->date('d M Y')
                    ->color(fn (Program $record) => $record->isExpired() ? 'danger' : 'success'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),
            ])

            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\DeleteAction::make()->label('Move to Trash'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Delete Forever')
                    ->requiresConfirmation(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }

    // To Display total number of Users linked to that institute
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('institution_id', auth()->user()->institution_id)->count();
    }
 
    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
