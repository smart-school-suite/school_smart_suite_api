<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableController;

Route::post('/generate', [SemesterTimetableController::class, 'generateTimetable'])->name('semesterTimetable.generate');
Route::get('/version/{versionId}/status', [SemesterTimetableController::class, 'getTimetableStatus'])->name('semesterTimetable.status');
Route::get('/version/{versionId}/errors', [SemesterTimetableController::class, 'getErrors'])->name('semesterTimetable.errors');
Route::get('/version/{versionId}/slots', [SemesterTimetableController::class, 'getTimetableSlots'])->name('semesterTimetable.slots');
Route::get('/version/{versionId}/payload', [SemesterTimetableController::class, 'getTimetableRequestPayload'])->name('semesterTimetable.payload');
Route::get('/version/{versionId}/parsed-diagnostics', [SemesterTimetableController::class, 'getParsedDiagnostics'])->name('parsedDiagnostics.get');
Route::get('/version/{versionId}/raw-diagnostics', [SemesterTimetableController::class, 'getRawDiagnostics'])->name('rawDiagnostic.get');
Route::post('/create/active', [SemesterTimetableController::class, 'createActiveSemesterTimetable'])->name('semesterTimetable.active.create');
Route::post('/test/test/preference-engine', [SemesterTimetableController::class, 'generateTimetableWithPreference'])->name('semesterTimetable.test.preference');
