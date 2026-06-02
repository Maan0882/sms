<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon  = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Access Control';
    protected static ?string $navigationLabel = 'Permissions';
    protected static ?int    $navigationSort  = 1;
    
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
        $columns = [
            Tables\Columns\TextColumn::make('name')
                ->label('Permission')
                ->searchable()
                ->sortable()
                ->fontFamily('mono')
                ->badge()
                ->color('gray')
                ->formatStateUsing(function (string $state) {
                    $parts = explode('.', $state);
                    if (count($parts) === 2) {
                        $resource = str_replace('_', ' ', $parts[0]);
                        $action = str_replace('_', ' ', $parts[1]);
                        return "can {$action} {$resource}";
                    }
                    return $state;
                }),
        ];

        try {
            $roles = \Spatie\Permission\Models\Role::all();
            foreach ($roles as $role) {
                $columns[] = Tables\Columns\IconColumn::make('role_' . $role->id)
                    ->label(ucfirst(str_replace('_', ' ', $role->name)))
                    ->boolean()
                    ->getStateUsing(fn (Permission $record): bool => $record->roles->contains('id', $role->id));
            }
        } catch (\Exception $e) {
            // Ignore in case DB is not set up
        }

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->label('Created')
            ->dateTime('d M Y')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->columns($columns)
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
}
