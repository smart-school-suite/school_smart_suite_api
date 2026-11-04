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
Route::get('/version/{versionId}/solutions', [SemesterTimetableController::class, 'getTimetableSolutions'])->name('semesterTimetable.solutions');
Route::post('/version/{versionId}/solutions/apply', [SemesterTimetableController::class, 'applySolutions'])->name('semesterTimetable.solutions.apply');
Route::post('/create/active', [SemesterTimetableController::class, 'createActiveSemesterTimetable'])->name('semesterTimetable.active.create');
Route::post('/version/{versionId}/activate', [SemesterTimetableController::class, 'activateTimetableVersion'])->name('semesterTimetable.version.activate');
Route::get('/version/{versionId}/activation-check', [SemesterTimetableController::class, 'checkTimetableActivation'])->name('semesterTimetable.version.activationCheck');
Route::get('/school-semester/{schoolSemesterId}/active-timetable', [SemesterTimetableController::class, 'getActiveTimetable'])->name('semesterTimetable.active.get');
Route::post('/test/test/preference-engine', [SemesterTimetableController::class, 'generateTimetableWithPreference'])->name('semesterTimetable.test.preference');
