<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Application extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'institution_id',
        'user_id', 'program_id', 'cohort_id',
        'status', 'remarks', 'documents',
        'submitted_at', 'reviewed_at', 'reviewed_by',
    ];

    protected $casts = [
        'documents'    => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }

    public function institution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
