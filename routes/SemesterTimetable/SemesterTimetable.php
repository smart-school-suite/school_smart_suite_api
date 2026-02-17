<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableController;

Route::post('/preference/generate', [SemesterTimetableController::class, 'generatePreferenceTimetable'])->name('semesterTimetable.preference.generate');
Route::post('/without-preference/generate', [SemesterTimetableController::class, 'generateFixedTimetable'])->name('semesterTimetable.fixed.generate');
Route::get('/{schoolSemesterId}/conversations', [SemesterTimetableController::class, 'getTimetableConversations'])->name('semesterTimetable.conversations.get');
