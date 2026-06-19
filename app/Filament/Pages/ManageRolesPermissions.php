<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManageRolesPermissions extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Access Control';
    protected static ?string $navigationLabel = 'Assign Roles & Permissions';
    protected static ?string $title           = 'Assign Roles & Permissions Globally';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.pages.manage-roles-permissions';

    public static function canAccess(): bool
    {
        // Only SuperAdmins can access this page.
        return auth()->user()->hasRole(['super_admin','admin']);
    }
    // ── Form state ─────────────────────────────────────────────────────

    public ?int    $selectedUserId       = null;
    public array   $selectedRoles        = [];
    public array   $selectedPermissions  = [];
    public ?string $successMessage       = null;

    // ── Load user data when user is selected ──────────────────────────

    public function updatedSelectedUserId(): void
    {
        if (! $this->selectedUserId) return;

        $user = User::find($this->selectedUserId);

        if ($user) {
            $this->selectedRoles       = $user->roles->pluck('id')->toArray();
            $this->selectedPermissions = $user->permissions->pluck('id')->toArray();
        }
    }

    // ── Sync roles to user ─────────────────────────────────────────────

    public function syncRoles(): void
    {
        $user = User::findOrFail($this->selectedUserId);

        // Protect super_admin role
        $superAdminRoleId = Role::where('name', 'super_admin')->first()?->id;
        if (
            in_array($superAdminRoleId, $this->selectedRoles) &&
            ! $user->isSuperAdmin()
        ) {
            Notification::make()
                ->title('Cannot assign super_admin role from here')
                ->body('Use the seeder to create Super Admin accounts.')
                ->danger()
                ->send();
            return;
        }

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $user->syncRoles($roles);

        Notification::make()
            ->title('Roles updated for ' . $user->name)
            ->success()
            ->send();
    }

    // ── Sync direct permissions to user ───────────────────────────────

    public function syncPermissions(): void
    {
        $user        = User::findOrFail($this->selectedUserId);
        $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();

        $user->syncPermissions($permissions);

        Notification::make()
            ->title('Permissions updated for ' . $user->name)
            ->success()
            ->send();
    }

    // ── Revoke everything from user ───────────────────────────────────

    public function revokeAll(): void
    {
        $user = User::findOrFail($this->selectedUserId);

        if ($user->isSuperAdmin()) {
            Notification::make()
                ->title('Cannot revoke Super Admin roles')
                ->danger()
                ->send();
            return;
        }

        $user->syncRoles([]);
        $user->syncPermissions([]);

        $this->selectedRoles       = [];
        $this->selectedPermissions = [];

        Notification::make()
            ->title('All roles & permissions revoked from ' . $user->name)
            ->warning()
            ->send();
    }

    // ── Page data for the view ─────────────────────────────────────────

    public function getSelectedUser(): ?User
    {
        return $this->selectedUserId ? User::find($this->selectedUserId) : null;
    }

    public function getAllRoles(): \Illuminate\Support\Collection
    {
        return Role::all();
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return Permission::all()->groupBy(function ($permission) {
            // Group by the prefix: user.view → "User"
            return ucfirst(explode('.', $permission->name)[0]);
        });
    }

    public function getAllUsers(): \Illuminate\Support\Collection
    {
        return User::with('roles')->get()->map(function ($user) {
            return [
                'id'    => $user->id,
                'label' => $user->name . ' (' . $user->email . ')',
                'roles' => $user->roles->pluck('name')->join(', '),
            ];
        });
    }
}
