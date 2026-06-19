<?php

namespace App\Filament\Resources\AdminManagement;

use App\Filament\Resources\AdminManagement\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationResource extends Resource
{
    protected static ?string $model           = Application::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Applications';
    protected static ?string $slug            = 'applications';
    protected static ?string $navigationLabel = 'Applications';
    protected static ?int    $navigationSort  = 3;

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
        if (!$institution || !\App\Models\ModeConfig::isResourceEnabled($institution->mode, 'applications')) {
            return false;
        }

        return $user->hasAnyRole(['admin']) || $user->hasPermissionTo('application.view');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Application Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Student')
                        ->options(User::role('student')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('program_id')
                        ->label('Program')
                        ->options(Program::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('cohort_id', null)),

                    Forms\Components\Select::make('cohort_id')
                        ->label('Cohort')
                        ->options(fn ($get) =>
                            Cohort::where('program_id', $get('program_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'approved'  => 'Approved',
                            'rejected'  => 'Rejected',
                            'withdrawn' => 'Withdrawn',
                        ])
                        ->default('pending')
                        ->required(),

                    Forms\Components\Textarea::make('remarks')
                        ->label('Admin Remarks')
                        ->rows(3)
                        ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('student.email')
                    ->label('Email')
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('cohort.name')
                    ->label('Cohort')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved'  => 'success',
                        'pending'   => 'warning',
                        'rejected'  => 'danger',
                        'withdrawn' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'rejected'  => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ]),

                Tables\Filters\SelectFilter::make('program')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),
            ])

            ->actions([
                // Approve action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Application $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function (Application $record) {
                        $record->update([
                            'status'      => 'approved',
                            'reviewed_at' => now(),
                            // 'reviewed_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Application approved')
                            ->success()
                            ->send();
                    }),

                // Reject action
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Application $record) => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Reason for rejection')
                            ->required(),
                    ])
                    ->action(function (Application $record, array $data) {
                        $record->update([
                            'status'      => 'rejected',
                            'remarks'     => $data['remarks'],
                            'reviewed_at' => now(),
                            // 'reviewed_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Application rejected')
                            ->warning()
                            ->send();
                    }),

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

                    // Bulk approve
                    Tables\Actions\BulkAction::make('approveAll')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update([
                            'status'      => 'approved',
                            'reviewed_at' => now(),
                            // 'reviewed_by' => auth()->id(),
                        ])),

                    // Bulk reject
                    Tables\Actions\BulkAction::make('rejectAll')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update([
                            'status'      => 'rejected',
                            'reviewed_at' => now(),
                            // 'reviewed_by' => auth()->id(),
                        ])),
                ]),
            ])

            ->defaultSort('submitted_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['student', 'program', 'cohort'])
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

}
