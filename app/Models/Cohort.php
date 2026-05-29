<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Cohort extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'program_id', 'name', 'code',
        'max_students', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
        'deleted_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
