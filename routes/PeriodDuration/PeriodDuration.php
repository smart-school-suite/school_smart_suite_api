<?php

use App\Http\Controllers\PeriodDuration\PeriodDurationController;
use Illuminate\Support\Facades\Route;

Route::post('/create', [PeriodDurationController::class, 'createPeriodDurationType'])->name('periodDuration.create');
Route::delete('{periodDurationId}/delete', [PeriodDurationController::class, 'deletePeriodDuration'])->name('periodDuration.delete');
Route::put('{periodDurationId}/update', [PeriodDurationController::class, 'updateDurationType'])->name('periodDuration.update');
Route::post('/activate', [PeriodDurationController::class, 'activatePeriodDuration'])->name('periodDuration.activate');
Route::post('/deactivate', [PeriodDurationController::class, 'deactivatePeriodDuration'])->name('periodDuration.deactivate');
Route::get('/exam-timetable', [PeriodDurationController::class, 'getExamTimetablePeriodDuration'])->name('periodDuration.examtimetable');
Route::get('/semester-timetable', [PeriodDurationController::class, 'getSemesterTimetablePeriodDuration'])->name('periodDuration.semesterTimetable');
Route::get('/resit-timetable', [PeriodDurationController::class, 'getResitTimetablePeriodDuration'])->name('periodDuration.resitTimetable');
Route::get('/all', [PeriodDurationController::class, 'getAllPeriodDuration'])->name('periodDuration');
Route::get('/{periodDurationId}', [PeriodDurationController::class, 'getPeriodDurationById'])->name('periodDuration.byID');
