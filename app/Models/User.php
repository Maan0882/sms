<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Permission\Traits\HasRoles;


// #[Fillable(['name', 'email', 'password'])]
// #[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements Auditable, FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'avatar_url',
    ];

    // What fields to track changes on
    protected array $auditInclude = [
        'name',
        'email',
        'is_active',
        'avatar_url',
    ];

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

    // Filament panel access control
    public function canAccessPanel(Panel $panel): bool
    {
        // Block deactivated accounts globally
        if (! $this->is_active) {
            return false;
        }
 
        return match ($panel->getId()) {
 
            // SuperAdmin panel: super_admin role only
            'superAdmin' => $this->hasRole('super_admin'),
 
            // Admin panel:
            //   ✅ NOT super_admin
            //   ✅ has at least one role assigned
            //   ✅ is_active already checked above
            'admin' => ! $this->hasRole('super_admin')
                       && $this->roles->isNotEmpty(),
 
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
}
