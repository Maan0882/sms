<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeConfig extends Model
{
    use HasFactory;

    protected $table = 'mode_configs';

    protected $fillable = [
        'mode',
        'resources',
    ];

    protected $casts = [
        'resources' => 'array',
    ];

    public static function isResourceEnabled(string $mode, string $resourceKey): bool
    {
        $config = static::where('mode', $mode)->first();
        if (!$config || !$config->resources) {
            return false;
        }
        return in_array($resourceKey, $config->resources);
    }
}
