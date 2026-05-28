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
        // Check if the admin explicitly has the required permission assigned
        return $user->hasPermissionTo('view_any_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_admin');
    }

    public function update(User $user, User $model): bool
    {
        // Admins are strictly forbidden from modifying Super Admins
        if ($model->isSuperAdmin()) return false;
        
        return $user->hasPermissionTo('update_admin');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) return false;
        
        return $user->hasPermissionTo('delete_admin');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('restore_admin');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) return false;
        
        return $user->hasPermissionTo('force_delete_admin');
    }
}
