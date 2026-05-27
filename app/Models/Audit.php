<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as AuditModel;

class Audit extends AuditModel
{
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ── Relationship back to the user who made the change ─────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Helper — what changed as a readable string ────────────────────

    public function getChangeSummaryAttribute(): string
    {
        $changes = [];

        foreach ($this->new_values as $field => $newValue) {
            $oldValue = $this->old_values[$field] ?? 'empty';

            // Clean up boolean values for display
            if (is_bool($newValue) || in_array($newValue, [0, 1])) {
                $newValue = $newValue ? 'Yes' : 'No';
                $oldValue = $oldValue ? 'Yes' : 'No';
            }

            $changes[] = "{$field}: {$oldValue} → {$newValue}";
        }

        return implode(', ', $changes) ?: 'No changes recorded';
    }
}
