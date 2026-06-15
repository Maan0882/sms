<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-widget';
    protected static ?int $sort = 0;

    // Full width banner at top
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = Auth::user();
        $hour = now()->hour;

        $greeting = match (true) {
            $hour >= 17 => 'Good evening',
            $hour >= 12 => 'Good afternoon',
            default     => 'Good morning',
        };

        return [
            'greeting' => $greeting,
            'name'     => $user->name,
            'date'     => now()->format('l, F j, Y'),
        ];
    }
}