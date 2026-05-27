<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon  = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Access Control';
    protected static ?string $navigationLabel = 'Permissions';
    protected static ?int    $navigationSort  = 2;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Permission Details')
                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Permission Name')
                            ->required()
                            ->unique(Permission::class, 'name', ignoreRecord: true)
                            ->placeholder('e.g. user.view, user.delete, role.create')
                            ->helperText('Use dot notation: resource.action')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('guard_name')
                            ->label('Guard')
                            ->default('web')
                            ->required()
                            ->helperText('Usually "web"'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Assign to Roles')
                    ->description('Which roles should have this permission')
                    ->schema([

                        Forms\Components\CheckboxList::make('roles')
                            ->label('')
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3)
                            ->gridDirection('row'),

                    ]),
            ]);
    }

    // ── TABLE ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Assigned To')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'warning',
                        'admin'       => 'danger',
                        'mentor'      => 'success',
                        'student'     => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(
                        fn ($state) => ucfirst(str_replace('_', ' ', $state))
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // ── PAGES ──────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
