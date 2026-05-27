<?php

namespace App\Providers;

//use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    // ── Register your Policies here ────────────────────────────────────
    protected $policies = [
        User::class => UserPolicy::class,

        // Add more as you build them:
        // Admin::class   => AdminPolicy::class,
        // Mentor::class  => MentorPolicy::class,
        // Student::class => StudentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ── THE KEY LINE ───────────────────────────────────────────────
        // Runs before every single Gate / Policy check in the entire app.
        // Returning true  = bypass everything, access granted.
        // Returning false = bypass everything, access denied.
        // Returning null  = continue to normal policy check.

        Gate::before(function (User $user, string $ability): bool|null {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}
