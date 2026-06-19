<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitutionResource\Pages;
use App\Models\Institution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationGroup = 'Institute Management';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Institution Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(Institution::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone')
                            ->placeholder('Enter Phone Number')
                            ->tel()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                            
                        Forms\Components\FileUpload::make('logo_url')
                            ->label('Logo')
                            ->image()
                            ->directory('institutions/logos')
                            ->getUploadedFileNameForStorageUsing(
                                fn (\Illuminate\Http\UploadedFile $file, Forms\Get $get): string => 
                                    (string) str($get('name'))->slug() . '-' . 'logo' . '.' . $file->getClientOriginalExtension()
                            )
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('subscription_id')
                            ->relationship('subscription', 'plan_name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->label('Subscription Plan'),

                        Forms\Components\DatePicker::make('subscription_expires_at')
                            ->label('Subscription Expiry Date')
                            ->nullable(),

                        Forms\Components\Select::make('subscription_status')
                            ->label('Subscription Status')
                            ->options([
                                'active' => 'Active',
                                'pending_renewal' => 'Pending Renewal',
                                'pending_cancellation' => 'Pending Cancellation',
                                'expired' => 'Expired',
                            ])
                            ->default('active')
                            ->required(),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Institution&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\TextColumn::make('name')
                    ->placeholder('Institution Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription.plan_name')
                    ->label('Subscription')
                    ->badge()
                    ->color('success')
                    ->placeholder('No Plan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription_status')
                    ->label('Sub Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'pending_renewal' => 'warning',
                        'pending_cancellation' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subscription_expires_at')
                    ->label('Sub Expires')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->placeholder("Institution's Email")
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('Phone Number')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Can add UserRelationManager here later if needed to assign admins
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitutions::route('/'),
            'create' => Pages\CreateInstitution::route('/create'),
            'edit' => Pages\EditInstitution::route('/{record}/edit'),
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
