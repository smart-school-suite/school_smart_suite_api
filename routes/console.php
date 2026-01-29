<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('semesters:update-statuses')
    ->daily()
    ->at('00:00')
    ->timezone('Africa/Douala')
    ->withoutOverlapping(3600)
    ->runInBackground()
    ->onOneServer();

Schedule::command('admin:activation-code-expire-reminder-notification')
    ->dailyAt('09:00')
    ->timezone('Africa/Douala')
    ->withoutOverlapping(3600)
    ->runInBackground()
    ->onOneServer();

Schedule::command('teacher:subscription-renewal-reminder-notification')
    ->dailyAt('10:00')
    ->timezone('Africa/Douala')
    ->withoutOverlapping(3600)
    ->runInBackground()
    ->onOneServer();

Schedule::command('student:subscription-renewal-reminder-notification')
    ->dailyAt('11:00')
    ->timezone('Africa/Douala')
    ->withoutOverlapping(3600)
    ->runInBackground()
    ->onOneServer();

Schedule::command('admin:activation-code-renewal-reminder-notification')
    ->dailyAt('12:00')
    ->timezone('Africa/Douala')
    ->withoutOverlapping(3600)
    ->runInBackground()
    ->onOneServer();

Schedule::command('create-system-academic-year-command')
    ->monthlyOn(1, '00:00')
    ->timezone('Africa/Douala')
    ->withoutOverlapping(3600)
    ->runInBackground()
    ->onOneServer();
