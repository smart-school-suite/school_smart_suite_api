<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Timetable\TimetableController;
use App\Http\Controllers\Timetable\AIGenTimetableController;


Route::post('/semesters/timetable/availability', [TimeTableController::class, 'createTimetableByAvailability'])
    ->name('semesters.timetable.availability.store');

Route::post('/semesters/timetable', [TimeTableController::class, 'createTimetable'])
    ->name('semesters.timetable.store');

Route::patch('/timetable/specialty', [TimeTableController::class, 'updateTimetable'])
    ->name('timetable.update');

Route::patch('/timetable/availability', [TimeTableController::class, 'updateTimetableByTeacherAvailability'])
    ->name('timetable.availability.update');

Route::post('/timetable/specialty', [TimeTableController::class, 'deleteTimetable'])
    ->name('timetable.delete');

Route::post('/timetable/generate', [TimeTableController::class, 'generateTimetable'])
    ->name('timetable.generate');

Route::delete('/timetable-entry/{entryId}', [TimeTableController::class, 'deleteTimeTableEntry'])
    ->name('timetable-entry.destroy');

Route::get('/timetable-entry/{entryId}', [TimeTableController::class, 'getTimetableDetails'])
    ->name('timetable-entry.show');

Route::get('/semesters/{semesterId}/specialties/{specialtyId}/instructor-availability', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty'])
    ->name('semesters.specialties.instructor-availability.index');

Route::get('/student', [TimeTableController::class, 'getTimetableStudent'])->name('get.student.timetable');
Route::post('/ai-generate-timetable', [AIGenTimetableController::class, 'generateTimetable'])->name('ai.generate.timetable');
