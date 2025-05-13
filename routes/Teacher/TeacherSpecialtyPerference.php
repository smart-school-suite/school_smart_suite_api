<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\TeacherSpecailtyPreferenceController;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->group(function () {
    // Get specialty preference for a specific teacher
    Route::get('/teachers/{teacherId}/specialty-preference', [TeacherSpecailtyPreferenceController::class, 'getTeacherSpecailtyPreference'])
        ->name('teachers.specialty-preference.show');
});
