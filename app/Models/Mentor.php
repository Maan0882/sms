<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mentor extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'user_id',
        'institution_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar',
        'designation',
        'expertise',
        'bio',
        'max_students',
        'status',
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
 
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'mentor_program');
    }
 
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
 
    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------
 
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
 
    public function scopeAvailable($query)
    {
        return $query->active()
                     ->withCount('students')
                     ->havingRaw('students_count < max_students');
    }
}
