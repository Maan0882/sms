<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Run backup every day at midnight ──────────────────────────────────
Schedule::command('backup:run')->dailyAt('00:00');

// ── Clean old backups every week ──────────────────────────────────────
Schedule::command('backup:clean')->weekly();

// ── Monitor backup health every morning ───────────────────────────────
Schedule::command('backup:monitor')->dailyAt('08:00');