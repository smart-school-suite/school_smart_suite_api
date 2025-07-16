<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\TeacherSpecailtyPreferenceController;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->group(function () {
    Route::middleware(['permission:teacher.teacher.view.specialty.peference|schoolAdmin.teacher.view.specialty.peference'])
    ->get('/teachers/{teacherId}/specialty-preference', [TeacherSpecailtyPreferenceController::class, 'getTeacherSpecailtyPreference'])
        ->name('teachers.specialty-preference.show');
    Route::get('/available-preferences/{teacherId}', [TeacherSpecailtyPreferenceController::class, "getTeacherAvialableSpecialtyPreference"]);

    Route::post('/remove-preference', [TeacherSpecailtyPreferenceController::class, 'removeTeacherSpecialtyPreference']);
});
