<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Program extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'institution_id',
        'name', 'code', 'description',
        'duration_months', 'max_students',
        'is_active', 'start_date', 'end_date',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'start_date'  => 'date',
        'end_date'    => 'date',
        'deleted_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function enrolledCount(): int
    {
        return $this->applications()->where('status', 'approved')->count();
    }

    public function institution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
