<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;

class UserRelationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No bindings needed.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamically add the `creator` relationship to the User model without modifying the model file.
        User::resolveRelationUsing('creator', function (User $user) {
            return $user->belongsTo(User::class, 'created_by');
        });
    }
}
?>
