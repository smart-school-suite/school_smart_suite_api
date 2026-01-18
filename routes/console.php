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
