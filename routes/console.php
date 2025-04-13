<?php

use Illuminate\Foundation\Inspiring;
use APP\Console\Commands\UpdatePastSchoolSemesters;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(UpdatePastSchoolSemesters::class)->daily();
