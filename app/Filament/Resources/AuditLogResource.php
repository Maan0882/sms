<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\Audit;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model           = Audit::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int    $navigationSort  = 1;
    
    // Audit logs are read-only — no creating or editing
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    // ── FORM — view only ───────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([]); // empty — no editing allowed
    }

    // ── TABLE ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Who did it
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->description(fn (Audit $record) => $record->user?->email ?? '—')
                    ->sortable()
                    ->default('System')
                    ->weight('semibold'),

                // Tables\Columns\TextColumn::make('user.email')
                //     ->label('Email')
                //     ->searchable()
                //     ->default('—')
                //     ->color('gray'),

                // What they did
                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                // Which model was affected
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Model')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(
                        fn ($state) => class_basename($state)
                        // Turns App\Models\User into just "User"
                    ),

                // Record ID affected
                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('Record ID')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),

                // Record Name
                Tables\Columns\TextColumn::make('auditable_name')
                    ->label('Name')
                    ->getStateUsing(fn (Audit $record) => $record->auditable?->name ?? '—')
                    ->description(fn (Audit $record) => $record->auditable?->email ?? '—')
                    ->color('gray'),

                // What changed
                Tables\Columns\TextColumn::make('change_summary')
                    ->label('Changes')
                    ->getStateUsing(fn (Audit $record) => $record->change_summary)
                    ->wrap()
                    ->limit(60)
                    ->tooltip(fn (Audit $record) => $record->change_summary)
                    ->color('gray'),

                // IP address
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // When it happened
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->since() // shows "2 hours ago"
                    ->tooltip(
                        fn (Audit $record) =>
                        $record->created_at->format('d M Y, h:i:s A')
                    ),

            ])

            ->filters([

                // Filter by action type
                Tables\Filters\SelectFilter::make('event')
                    ->label('Action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                // Filter by model type
                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label('Model')
                    ->options([
                        'App\Models\User'    => 'User',
                        'App\Models\Admin'   => 'Admin',
                        'App\Models\Mentor'  => 'Mentor',
                        'App\Models\Student' => 'Student',
                    ]),

                // Filter by date range
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),

            ])

            // View full audit detail in a slide-over panel
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Audit Log Detail')
                    ->modalContent(fn (Audit $record) => view(
                        'filament.modals.audit-detail',
                        ['audit' => $record]
                    )),
            ])

            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
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
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
