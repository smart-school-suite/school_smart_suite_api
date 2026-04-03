<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableController;

Route::post('/generate', [SemesterTimetableController::class, 'generateTimetable'])->name('semesterTimetable.generate');
Route::get('/diagnostics/{timetableVersionId}', [SemesterTimetableController::class, 'getParsedTimetableDiagnostics'])->name('semesterTimetable.diagnostics');
Route::post('/create/active', [SemesterTimetableController::class, 'createActiveSemesterTimetable'])->name('semesterTimetable.active.create');
Route::post('/test/test/preference-engine', [SemesterTimetableController::class, 'generateTimetableWithPreference'])->name('semesterTimetable.test.preference');
