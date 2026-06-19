<?php

namespace App\Filament\Resources\AdminManagement;

use App\Filament\Resources\AdminManagement\StudentResource\Pages;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class StudentResource extends Resource
{
    protected static ?string $model           = Student::class;
    protected static ?string $navigationIcon  = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'People';
    protected static ?string $navigationLabel = 'Students';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $slug            = 'students';
    protected static ?string $recordTitleAttribute = 'full_name';
    protected static ?string $modelLabel      = 'Student';
    protected static ?string $pluralModelLabel = 'Students';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return false;
        }

        $institution = $user->institution;
        if (!$institution || $institution->mode !== 'student_management') {
            return false;
        }

        return $user->hasAnyRole(['admin']) || $user->hasPermissionTo('student.view');
    }
    // ── FORM ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Personal Information')
                    ->description('Student identity and contact details.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(100),
 
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(100),
 
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(table: User::class, column: 'email', ignorable: fn ($record) => $record?->user)
                            ->maxLength(255),
 
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText('Leave blank to keep existing password when editing.')
                            ->maxLength(255),
 
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
 
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->maxDate(now()->subYears(10)),
 
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male'   => 'Male',
                                'female' => 'Female',
                                'other'  => 'Other',
                            ]),
 
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('student-avatars')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
 
                Forms\Components\Section::make('Enrollment Details')
                    ->description('Academic program, cohort, and mentor assignment.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Select::make('program_id')
                            ->label('Program')
                            ->relationship('program', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
 
                        Forms\Components\Select::make('cohort_id')
                            ->label('Cohort')
                            ->relationship(
                                name: 'cohort',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query, Forms\Get $get) =>
                                    $query->where('program_id', $get('program_id'))
                            )
                            ->searchable()
                            ->preload(),
 
                        Forms\Components\Select::make('mentor_id')
                            ->label('Assigned Mentor')
                            ->relationship(
                                name: 'mentor',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->active()->role('mentor')
                            )
                            ->searchable(['name'])
                            ->preload()
                            ->helperText('Only active mentors are shown.'),
 
                        Forms\Components\TextInput::make('student_id')
                            ->label('Student ID / Roll Number')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
 
                        Forms\Components\DatePicker::make('enrollment_date')
                            ->default(today())
                            ->required(),
 
                        Forms\Components\Select::make('enrollment_status')
                            ->options([
                                'enrolled'   => 'Enrolled',
                                'pending'    => 'Pending',
                                'suspended'  => 'Suspended',
                                'graduated'  => 'Graduated',
                                'dropped'    => 'Dropped Out',
                            ])
                            ->default('enrolled')
                            ->required(),
 
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
 
                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\TextInput::make('address_line1')
                            ->label('Address Line 1')
                            ->maxLength(255)
                            ->columnSpanFull(),
 
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
 
                        Forms\Components\TextInput::make('state')
                            ->maxLength(100),
 
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(20),
 
                        Forms\Components\Select::make('country')
                            ->searchable()
                            ->optionsLimit(300)
                            ->options(fn () => cache()->remember('countries_list', now()->addDay(), function () {
                                return \Illuminate\Support\Facades\Http::withoutVerifying()
                                    ->get('https://restcountries.com/v3.1/all?fields=name,cca2')
                                    ->collect()
                                    ->mapWithKeys(fn ($c) => [($c['cca2'] ?? '') => ($c['name']['common'] ?? '')])
                                    ->filter(fn ($name, $code) => !empty($name) && !empty($code))
                                    ->sort()
                                    ->toArray();
                            })),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
        ]);
    }

    // ── TABLE ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=F59E0B&background=FEF3C7'),
 
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
 
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->email),
 
                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->searchable()
                    ->badge()
                    ->color('info'),
 
                Tables\Columns\TextColumn::make('cohort.name')
                    ->label('Cohort')
                    ->searchable()
                    ->toggleable(),
 
                Tables\Columns\TextColumn::make('mentor.name')
                    ->label('Mentor')
                    ->searchable()
                    ->toggleable(),
 
                Tables\Columns\TextColumn::make('enrollment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'enrolled'  => 'success',
                        'pending'   => 'warning',
                        'suspended' => 'danger',
                        'graduated' => 'info',
                        'dropped'   => 'gray',
                    }),
 
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->date('d M Y')
                    ->sortable(),
 
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('enrollment_status')
                    ->label('Status')
                    ->options([
                        'enrolled'  => 'Enrolled',
                        'pending'   => 'Pending',
                        'suspended' => 'Suspended',
                        'graduated' => 'Graduated',
                        'dropped'   => 'Dropped Out',
                    ]),
 
                Tables\Filters\SelectFilter::make('program')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),
 
                Tables\Filters\SelectFilter::make('cohort')
                    ->relationship('cohort', 'name')
                    ->searchable()
                    ->preload(),
 
                Tables\Filters\SelectFilter::make('mentor')
                    ->relationship('mentor', 'name')
                    ->searchable()
                    ->preload(),
 
                Tables\Filters\Filter::make('enrolled_this_month')
                    ->label('Enrolled This Month')
                    ->query(fn (Builder $query) => $query->whereMonth('enrollment_date', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
 
                Tables\Actions\Action::make('reassign_mentor')
                    ->label('Reassign Mentor')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('mentor_id')
                            ->label('New Mentor')
                            ->relationship('mentor', 'name', fn (Builder $query) =>
                                $query->where('is_active', 1))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['mentor_id' => $data['mentor_id']]);
                    }),
 
                Tables\Actions\Action::make('change_status')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->form([
                        Forms\Components\Select::make('enrollment_status')
                            ->options([
                                'enrolled'  => 'Enrolled',
                                'pending'   => 'Pending',
                                'suspended' => 'Suspended',
                                'graduated' => 'Graduated',
                                'dropped'   => 'Dropped Out',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['enrollment_status' => $data['enrollment_status']]);
                    }),
 
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\DeleteAction::make()->label('Move to Trash'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Delete Forever')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_assign_mentor')
                        ->label('Assign Mentor')
                        ->icon('heroicon-o-academic-cap')
                        ->form([
                            Forms\Components\Select::make('mentor_id')
                                ->label('Mentor')
                                ->relationship('mentor', 'name', fn (Builder $query) => $query->where('status', 'active'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(fn ($records, array $data) => $records->each->update(['mentor_id' => $data['mentor_id']]))
                        ->requiresConfirmation(),
 
                    Tables\Actions\BulkAction::make('bulk_status_change')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->form([
                            Forms\Components\Select::make('enrollment_status')
                                ->options([
                                    'enrolled'  => 'Enrolled',
                                    'pending'   => 'Pending',
                                    'suspended' => 'Suspended',
                                    'graduated' => 'Graduated',
                                    'dropped'   => 'Dropped Out',
                                ])
                                ->required(),
                        ])
                        ->action(fn ($records, array $data) => $records->each->update(['enrollment_status' => $data['enrollment_status']]))
                        ->requiresConfirmation(),
 
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
 
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Student Profile')
                    ->schema([
                        Infolists\Components\ImageEntry::make('avatar')
                            ->circular()
                            ->size(80),
 
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name')
                            ->weight('bold'),
 
                        Infolists\Components\TextEntry::make('student_id')
                            ->label('Student ID')
                            ->badge()
                            ->color('gray'),
 
                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-m-envelope')
                            ->copyable(),
 
                        Infolists\Components\TextEntry::make('phone')
                            ->icon('heroicon-m-phone'),
 
                        Infolists\Components\TextEntry::make('enrollment_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'enrolled'  => 'success',
                                'pending'   => 'warning',
                                'suspended' => 'danger',
                                'graduated' => 'info',
                                default     => 'gray',
                            }),
                    ])
                    ->columns(3),
 
                Infolists\Components\Section::make('Enrollment')
                    ->schema([
                        Infolists\Components\TextEntry::make('program.name'),
                        Infolists\Components\TextEntry::make('cohort.name'),
                        Infolists\Components\TextEntry::make('mentor.name')
                            ->label('Assigned Mentor'),
                        Infolists\Components\TextEntry::make('enrollment_date')
                            ->date('d M Y'),
                    ])
                    ->columns(2),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        if (auth()->check() && auth()->user()->hasRole('admin') && auth()->user()->institution_id) {
            $query->where('institution_id', auth()->user()->institution_id);
        }

        return $query;
    }
    
    public static function getRelationManagers(): array
    {
        return [
            // RelationManagers\EvaluationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    // protected static function afterCreate(Model $record): void
    // {
    //     $record->assignRole('student');
    // }
     // Removed invalid afterCreate hook

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('enrollment_status', 'enrolled')->count();
    }
 
    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
