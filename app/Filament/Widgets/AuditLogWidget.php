<?php

namespace App\Filament\Widgets;

use App\Models\Audit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AuditLogWidget extends BaseWidget
{
    protected static ?int    $sort    = 4;
    protected static ?string $heading = 'Recent Activity';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Audit::query()->with('user')->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->default('System')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Model')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('Record ID')
                    ->fontFamily('mono')
                    ->color('gray'),
                
                 // Record Name
                Tables\Columns\TextColumn::make('auditable_name')
                    ->label('Name')
                    ->getStateUsing(fn (Audit $record) => $record->auditable?->name ?? $record->auditable?->first_name.' '.$record->auditable?->last_name ?? '—')
                    ->description(fn (Audit $record) => $record->auditable?->email ?? '—')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->tooltip(fn (Audit $record) => $record->created_at->format('d M Y, h:i:s A'))
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}

