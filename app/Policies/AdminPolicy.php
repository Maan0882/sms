<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    // Super Admin bypasses everything
    public function before(User $user, string $ability): bool|null
    {
        if (! $user->is_active)    return false;
        if ($user->isSuperAdmin()) return true;
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Admin cannot edit Super Admin
        if ($model->isSuperAdmin()) return false;
        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) return false;
        return $user->isAdmin();
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) return false;
        return $user->isAdmin();
    }
}
