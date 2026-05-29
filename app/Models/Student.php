<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'user_id',
        'institution_id',
        'program_id',
        'cohort_id',
        'mentor_id',
        'student_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar',
        'date_of_birth',
        'gender',
        'address_line1',
        'city',
        'state',
        'postal_code',
        'country',
        'enrollment_date',
        'enrollment_status',
        'notes',
    ];
 
    protected $casts = [
        'date_of_birth'   => 'date',
        'enrollment_date' => 'date',
    ];
 
    protected $appends = ['full_name'];
 
    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------
 
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
 
    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
 
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
 
    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
 
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Mentor::class, 'mentor_id');
    }
 
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
 
    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------
 
    public function scopeEnrolled($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }
 
    public function scopeWithoutMentor($query)
    {
        return $query->whereNull('mentor_id');
    }
}
