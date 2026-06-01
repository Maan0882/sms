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

        $valuesToIterate = $this->event === 'deleted' ? $this->old_values : $this->new_values;
        if (!is_array($valuesToIterate)) {
            $valuesToIterate = [];
        }

        foreach ($valuesToIterate as $field => $value) {
            $newValue = $this->event === 'deleted' ? null : $value;
            $oldValue = $this->old_values[$field] ?? null;

            if ($this->event === 'created') {
                $oldValue = 'empty';
            } elseif ($this->event === 'deleted') {
                $newValue = 'deleted';
            }

            // Clean up boolean values for display
            if (is_bool($newValue) || in_array($newValue, [0, 1], true)) {
                $newValue = $newValue ? 'Yes' : 'No';
            }
            if (is_bool($oldValue) || in_array($oldValue, [0, 1], true)) {
                $oldValue = $oldValue ? 'Yes' : 'No';
            }

            if (is_array($newValue)) $newValue = json_encode($newValue);
            if (is_array($oldValue)) $oldValue = json_encode($oldValue);
            
            if ($oldValue === null) $oldValue = 'empty';
            if ($newValue === null) $newValue = 'empty';

            $changes[] = "{$field}: {$oldValue} → {$newValue}";
        }

        $summary = implode(', ', $changes);

        if (empty($summary)) {
            if ($this->event === 'updated' && class_basename($this->auditable_type) === 'User') {
                return 'Password changed';
            }
            return 'No changes recorded';
        }

        return $summary;
    }
}
