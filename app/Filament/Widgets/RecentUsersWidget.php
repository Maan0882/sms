<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentUsersWidget extends BaseWidget
{
    protected static ?int    $sort    = 3;
    protected static ?string $heading = 'Recently Joined Users';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        // Only Superadmins will ever see this widget on the unified dashboard
        return auth()->user()->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->latest()->limit(8))
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(
                        fn (User $user) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random'
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->weight('semibold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'warning',
                        'admin'       => 'danger',
                        'mentor'      => 'success',
                        'student'     => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->since()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (User $record) => UserResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
