<?php

namespace App\Filament\Resources\AdminManagement;

use App\Filament\Resources\AdminManagement\CohortResource\Pages;
use App\Models\Cohort;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CohortResource extends Resource
{
    protected static ?string $model           = Cohort::class;
    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Programs';
    protected static ?string $navigationLabel = 'Cohorts';
    protected static ?string $slug            = 'cohorts';
    protected static ?int    $navigationSort  = 5;

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'admin']) || auth()->user()->hasPermissionTo('cohort.view');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Cohort Details')
                ->schema([
                    Forms\Components\Select::make('program_id')
                        ->label('Program')
                        ->options(Program::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('name')
                        ->label('Cohort Name')
                        ->required()
                        ->placeholder('e.g. Batch 2024-A'),

                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(Cohort::class, 'code', ignoreRecord: true)
                        ->placeholder('e.g. BCA2024-A'),

                    Forms\Components\TextInput::make('max_students')
                        ->label('Max Students')
                        ->numeric()
                        ->default(30)
                        ->required(),

                    Forms\Components\DatePicker::make('start_date')->required(),
                    Forms\Components\DatePicker::make('end_date')->after('start_date'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active Cohort')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->weight('semibold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Students')
                    ->counts('applications')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('d M Y'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),
            ])

            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListCohorts::route('/'),
            'create' => Pages\CreateCohort::route('/create'),
            'edit' => Pages\EditCohort::route('/{record}/edit'),
        ];
    }
}
