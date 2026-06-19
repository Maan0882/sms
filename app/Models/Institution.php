<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'phone',
        'address',
        'logo_url',
        'is_active',
        'subscription_id',
        'subscription_expires_at',
        'subscription_status',
        'mode',
    ];

    public function isStudentManagement(): bool
    {
        return $this->mode === 'student_management';
    }

    public function isInternshipManagement(): bool
    {
        return $this->mode === 'internship_management';
    }

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_expires_at' => 'date',
    ];

    public function subscription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function mentors(): HasMany
    {
        return $this->hasMany(Mentor::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
}
