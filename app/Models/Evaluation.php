<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Evaluation extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'student_id', 'mentor_id', 'program_id', 'cohort_id',
        'title', 'score', 'max_score', 'grade',
        'feedback', 'status', 'evaluated_at',
    ];

    protected $casts = [
        'score'         => 'decimal:2',
        'max_score'     => 'decimal:2',
        'evaluated_at'  => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function getPercentageAttribute(): float
    {
        if (! $this->max_score) return 0;
        return round(($this->score / $this->max_score) * 100, 2);
    }
}