<?php

use Illuminate\Foundation\Inspiring;
use APP\Console\Commands\UpdatePastSchoolSemesters;
use App\Console\Commands\SendBirthdayWishes;
use App\Console\Commands\SendExamGoodLuckWishes;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(UpdatePastSchoolSemesters::class)->daily();
Schedule::command(SendBirthdayWishes::class)->dailyAt('00:01');
Schedule::command(SendExamGoodLuckWishes::class);
