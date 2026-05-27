<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management'; // matches your panel group
    protected static ?string $navigationLabel = 'All Users';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $recordTitleAttribute = 'name';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->description('Primary user details')
                    ->icon('heroicon-o-user-circle')
                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            // ignoreRecord: true means ignore current user when editing
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('avatar_url')
                            ->label('Profile Photo')
                            ->image()
                            ->directory('avatars')      // saves to storage/app/public/avatars/
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('200')
                            ->imageResizeTargetHeight('200'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Account')
                            ->default(true)
                            ->helperText('Inactive users cannot log in'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Password')
                    ->description('Leave blank when editing to keep current password')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            // Only hash if something was typed
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            // Only save if something was typed
                            ->dehydrated(fn ($state) => filled($state))
                            // Required only when creating, not editing
                            ->required(fn (string $operation) => $operation === 'create')
                            ->minLength(8)
                            ->confirmed(), // must match password_confirmation

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(false), // never save this field to DB

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Roles & Permissions')
                    ->description('Assign Spatie roles to this user')
                    ->icon('heroicon-o-shield-check')
                    ->schema([

                        Forms\Components\Select::make('roles')
                            ->label('Assign Role')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('A user can have multiple roles'),

                    ])
                    ->columns(1)
            ]);
    }

    // ── TABLE ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Avatar circle
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(
                        fn (User $user) =>
                        'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random'
                    ),

                // Name — bold, searchable
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                // Email — copyable
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-m-envelope'),

                // Role badge with colour per role
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
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

                // Toggle active/inactive inline in the table
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),

                // Joined date — hidden by default, toggleable
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                // Filter by role
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter by Role')
                    ->multiple()
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin'       => 'Admin',
                        'mentor'      => 'Mentor',
                        'student'     => 'Student',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['values'])) return $query;
                        return $query->role($data['values']); // Spatie scope
                    }),

                // Filter by active status
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Account Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All users'),
            ])
            ->actions([
                // Edit user
                Tables\Actions\EditAction::make(),

                // Deactivate / Activate toggle
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (User $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        if ($record->isSuperAdmin()) {
                            Notification::make()
                                ->title('Cannot deactivate a Super Admin')
                                ->danger()
                                ->send();
                            return;
                        }
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title('User ' . ($record->is_active ? 'activated' : 'deactivated'))
                            ->success()
                            ->send();
                    }),

                // Delete — protected
                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record, Tables\Actions\DeleteAction $action) {
                        if ($record->isSuperAdmin()) {
                            Notification::make()
                                ->title('Cannot delete a Super Admin account')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($records) => $records->each->update(['is_active' => true]))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update(['is_active' => false]))
                    ->deselectRecordsAfterCompletion(),

            ]),
        ])
        ->defaultSort('created_at', 'desc')
        ->striped()
        ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // ── GLOBAL SEARCH ──────────────────────────────────────────────────

    // public static function getGlobalSearchResultTitle(User $record): string
    // {
    //     return $record->name;
    // }

    // public static function getGlobalSearchResultDetails(User $record): array
    // {
    //     return [
    //         'Email' => $record->email,
    //         'Role'  => $record->roles->pluck('name')->join(', '),
    //     ];
    // }

    // public static function getGloballySearchableAttributes(): array
    // {
    //     return ['name', 'email'];
    // }
}
