<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionResource extends Resource
{
    protected static ?string $model           = Subscription::class;
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Subscriptions';
    protected static ?int    $navigationSort  = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin') || auth()->user()->hasPermissionTo('subscription.view');
    }
    
    // ── FORM ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Plan Details')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Forms\Components\TextInput::make('plan_name')
                        ->label('Plan Name')
                        ->required()
                        ->placeholder('e.g. Basic, Pro, Enterprise'),

                    Forms\Components\TextInput::make('plan_code')
                        ->label('Plan Code')
                        ->required()
                        ->unique(Subscription::class, 'plan_code', ignoreRecord: true)
                        ->placeholder('e.g. basic, pro, enterprise')
                        ->helperText('Lowercase, no spaces'),

                    Forms\Components\TextInput::make('price')
                        ->label('Monthly Price (₹)')
                        ->numeric()
                        ->prefix('₹')
                        ->required(),

                    Forms\Components\Select::make('billing_cycle')
                        ->label('Billing Cycle')
                        ->options([
                            'monthly' => 'Monthly',
                            '6_months' => '6 Months',
                            'yearly'  => 'Yearly',
                        ])
                        ->required(),

                    Forms\Components\DatePicker::make('expires_at')
                        ->label('Expiry Date')
                        ->nullable(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active Plan')
                        ->default(true),
                ])
                ->columns(2),

            Forms\Components\Section::make('Limits')
                ->description('How many users this plan allows')
                ->icon('heroicon-o-users')
                ->schema([
                    Forms\Components\TextInput::make('max_admins')
                        ->label('Max Admins')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    Forms\Components\TextInput::make('max_mentors')
                        ->label('Max Mentors')
                        ->numeric()
                        ->default(5)
                        ->required(),

                    Forms\Components\TextInput::make('max_students')
                        ->label('Max Students')
                        ->numeric()
                        ->default(50)
                        ->required(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Features')
                ->description('Select the features included in this plan')
                ->schema([
                    Forms\Components\CheckboxList::make('features')
                        ->label('Included Features')
                        ->options([
                            'programs_cohorts'   => 'Programs & Cohorts Management',
                            'applications'       => 'Admissions & Applications Management',
                            'evaluations'        => 'Student Evaluations & Grading',
                            'mentors'            => 'Mentors Directory & Management',
                            'students'           => 'Students & Cohort Enrollment',
                            'audit_logs'         => 'Security & Audit Logs Access',
                            'backups'            => 'System Backups Access',
                            'reports_analytics'  => 'Reports & Analytics Dashboard Access',
                        ])
                        ->columns(2)
                        ->gridDirection('row'),
                ]),
        ]);
    }

    // ── TABLE ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan_name')
                    ->label('Plan')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan_code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label('Billing')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'yearly' => 'success',
                        '6_months' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        '6_months' => '6 Months',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('max_students')
                    ->label('Max Students')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Subscription $record) =>
                        $record->isExpired() ? 'danger' :
                        ($record->daysUntilExpiry() <= 7 ? 'warning' : 'success')
                    ),

                Tables\Columns\TextColumn::make('status_badge')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'         => 'success',
                        'expiring_soon'  => 'warning',
                        'expired'        => 'danger',
                        'inactive'       => 'gray',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),
            ])

            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->options(['monthly' => 'Monthly', '6_months' => '6 Months', 'yearly' => 'Yearly']),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InstitutionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
