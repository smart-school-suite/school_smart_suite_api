<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\Teacher\TeacherSpecialtyPreferenceController;
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->group(function () {
    Route::middleware(['permission:teacher.teacher.view.specialty.peference|schoolAdmin.teacher.view.specialty.peference'])
    ->get('/teachers/{teacherId}/specialty-preference', [TeacherSpecialtyPreferenceController::class, 'getTeacherSpecailtyPreference'])
        ->name('teachers.specialty-preference.show');
    Route::get('/available-preferences/{teacherId}', [TeacherSpecialtyPreferenceController::class, "getTeacherAvialableSpecialtyPreference"]);

    Route::post('/remove-preference', [TeacherSpecialtyPreferenceController::class, 'removeTeacherSpecialtyPreference']);

    Route::post('/bulk-add-preference', [TeacherSpecialtyPreferenceController::class, 'bulkAddTeacherSpecialtyPreference'])->name('bulk.add.teacher.specialtypreference');
    Route::post('/bulk-remove-preference', [TeacherSpecialtyPreferenceController::class, 'bulkRemoveTeacherSpecialtyPreference'])->name('bulk.remove.teacherSpecialtyPreference');
});
