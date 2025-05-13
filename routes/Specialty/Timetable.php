<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeTableController;

// Create a new timetable based on availability for a specific semester
Route::post('/semesters/{semesterId}/timetable/availability', [TimeTableController::class, 'createTimetableByAvailability'])
    ->name('semesters.timetable.availability.store');

// Create a new timetable for a specific semester
Route::post('/semesters/{semesterId}/timetable', [TimeTableController::class, 'createTimetable'])
    ->name('semesters.timetable.store');

// Update a specific timetable entry
Route::put('/timetable-entries/{timetableId}', [TimeTableController::class, 'updateTimetable'])
    ->name('timetable-entries.update');

// Update timetable based on teacher availability
Route::put('/timetable/availability', [TimeTableController::class, 'updateTimetableByTeacherAvailability'])
    ->name('timetable.availability.update');

// Delete the entire timetable for a specific student batch, specialty, level, and semester
Route::delete('/student-batches/{studentBatchId}/specialties/{specialtyId}/levels/{levelId}/semesters/{semesterId}/timetable', [TimeTableController::class, 'deleteTimetable'])
    ->name('student-batches.specialties.levels.semesters.timetable.destroy');

// Generate a timetable (requires request parameters to specify criteria)
Route::get('/timetable/generate', [TimeTableController::class, 'generateTimetable'])
    ->name('timetable.generate');

// Delete a specific timetable entry
Route::delete('/timetable-entries/{entryId}', [TimeTableController::class, 'deleteTimeTableEntry'])
    ->name('timetable-entries.destroy');

// Get details of a specific timetable entry
Route::get('/timetable-entries/{entryId}', [TimeTableController::class, 'getTimetableDetails'])
    ->name('timetable-entries.show');

// Get instructor availability for a specific semester and specialty
Route::get('/semesters/{semesterId}/specialties/{specialtyId}/instructor-availability', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty'])
    ->name('semesters.specialties.instructor-availability.index');
