<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Subscription extends Model implements Auditable
{
   use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'plan_name',
        'plan_code',
        'price',
        'billing_cycle',
        'max_admins',
        'max_mentors',
        'max_students',
        'is_active',
        'expires_at',
        'features',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'date',
        'features'   => 'array',
        'deleted_at' => 'datetime',
    ];

    // ── Helpers ────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): int
    {
        if (! $this->expires_at) return 9999;
        return max(0, now()->diffInDays($this->expires_at, false));
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->isExpired())    return 'expired';
        if (! $this->is_active)   return 'inactive';
        if ($this->daysUntilExpiry() <= 7) return 'expiring_soon';
        return 'active';
    }

    public function institutions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Institution::class);
    }
}
