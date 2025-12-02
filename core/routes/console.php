<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Auto-check for pending migrations (check only, don't auto-run in production)
Schedule::command('migrate:auto --check-only')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/migration-check.log'));

// Auto-run migrations only in non-production environments
if (app()->environment(['local', 'staging', 'development'])) {
    Schedule::command('migrate:auto')
        ->hourly()
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/migration-auto.log'));
}
