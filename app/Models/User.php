<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Permission\Traits\HasRoles;
// use Laravel\Sanctum\HasApiTokens;

// #[Fillable(['name', 'email', 'password'])]
// #[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements Auditable, FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'avatar_url',
        'institution_id',
    ];

    // // What fields to track changes on
    // protected array $auditInclude = [
    //     'name',
    //     'email',
    //     'is_active',
    //     'avatar_url',
    // ];

    // What fields to never track (sensitive)
    protected array $auditExclude = [
        'password',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

 // ── Convenience helpers ────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isMentor(): bool
    {
        return $this->hasRole('mentor');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role); // provided by HasRoles
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Block deactivated accounts globally
        if (! $this->is_active) {
            return false;
        }
 
        return match ($panel->getId()) {
            'app' => true, // All active users can access the main app panel. Roles/Policies handle specific resource access.
            default => false,
        };
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
 
    public function createdUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function institution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function mentor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Mentor::class, 'user_id', 'id');
    }

    public function getTenants(Panel $panel): array|Collection
    {
        // Super Admins don't have a specific institution to manage in the admin panel by default,
        // or we could let them manage all. For now, if they log into the Admin panel,
        // we can return all institutions so they can switch between them, or just their assigned one.
        if ($this->hasRole('super_admin')) {
            return Institution::all();
        }
        
        return $this->institution ? collect([$this->institution]) : collect([]);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        return $this->institution_id === $tenant->id;
    }
}
