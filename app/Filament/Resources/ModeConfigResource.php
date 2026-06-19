<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModeConfigResource\Pages;
use App\Models\ModeConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModeConfigResource extends Resource
{
    protected static ?string $model = ModeConfig::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Mode Configurations';
    protected static ?int    $navigationSort  = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mode Details')
                    ->schema([
                        Forms\Components\TextInput::make('mode')
                            ->label('System Mode')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->required()
                            ->placeholder('e.g. training_mode')
                            ->helperText('Lowercase, alphanumeric, and underscores only')
                            ->unique(ModeConfig::class, 'mode', ignoreRecord: true),
                    ]),

                Forms\Components\Section::make('Resource Visibility')
                    ->description('Select which resources are active and visible in this mode.')
                    ->schema([
                        Forms\Components\CheckboxList::make('resources')
                            ->label('Active Resources')
                            ->options([
                                'programs' => 'Programs Management',
                                'cohorts' => 'Cohorts Management',
                                'students' => 'Students Management',
                                'evaluations' => 'Evaluations & Grading',
                                'applications' => 'Applications & Admissions',
                            ])
                            ->required()
                            ->columns(2)
                            ->gridDirection('row'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mode')
                    ->label('System Mode')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'student_management' => 'info',
                        'internship_management' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'student_management' => 'Student Management',
                        'internship_management' => 'Internship Management',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('resources')
                    ->label('Enabled Resources')
                    ->badge()
                    ->color('success')
                    ->separator(', ')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->filters([])
             ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModeConfigs::route('/'),
            'create' => Pages\CreateModeConfig::route('/create'),
            'edit' => Pages\EditModeConfig::route('/{record}/edit'),
        ];
    }
}
