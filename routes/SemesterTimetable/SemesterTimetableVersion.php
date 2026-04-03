<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableVersionController;

Route::post('/create', [SemesterTimetableVersionController::class, 'createTimetableVersion']);
Route::get('/school-semester/{schoolSemesterId}', [SemesterTimetableVersionController::class, 'getTimetableVersions']);
Route::delete('/{versionId}', [SemesterTimetableVersionController::class, 'deleteTimetableVersion']);
Route::get('/{versionId}/timetable-slots', [SemesterTimetableVersionController::class, 'getSemesterTimetableSlotsVersionId']);
