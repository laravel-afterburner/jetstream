<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule audit log archiving daily at 2 AM
Schedule::command('afterburner:audit-archive --force')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->description('Archive old audit logs based on retention period');

// Schedule announcement email sending every minute
Schedule::command('announcements:send-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->description('Send scheduled announcement emails');
