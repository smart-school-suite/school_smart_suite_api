<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeTableController;

// Create a new timetable based on availability for a specific semester
Route::middleware(['permission:schoolAdmin.specialty.timetable.create'])->post('/semesters/{semesterId}/timetable/availability', [TimeTableController::class, 'createTimetableByAvailability'])
    ->name('semesters.timetable.availability.store');

// Create a new timetable for a specific semester
Route::middleware(['permission:schoolAdmin.specialty.timetable.create'])->post('/semesters/{semesterId}/timetable', [TimeTableController::class, 'createTimetable'])
    ->name('semesters.timetable.store');

// Update a specific timetable entry
Route::middleware(['permission:schoolAdmin.specialty.timetable.update'])->put('/timetable-entries/{timetableId}', [TimeTableController::class, 'updateTimetable'])
    ->name('timetable-entries.update');

// Update timetable based on teacher availability
Route::middleware(['permission:schoolAdmin.specialty.timetable.update'])->put('/timetable/availability', [TimeTableController::class, 'updateTimetableByTeacherAvailability'])
    ->name('timetable.availability.update');

// Delete the entire timetable for a specific student batch, specialty, level, and semester
Route::middleware(['permission:schoolAdmin.specialty.timetable.delete'])->delete('/student-batches/{studentBatchId}/specialties/{specialtyId}/levels/{levelId}/semesters/{semesterId}/timetable', [TimeTableController::class, 'deleteTimetable'])
    ->name('student-batches.specialties.levels.semesters.timetable.destroy');

// Generate a timetable (requires request parameters to specify criteria)
Route::middleware(['permission:schoolAdmin.specialty.timetable.view'])->get('/timetable/generate', [TimeTableController::class, 'generateTimetable'])
    ->name('timetable.generate');

// Delete a specific timetable entry
Route::middleware(['permission:schoolAdmin.specialty.timetable.delete'])->delete('/timetable-entries/{entryId}', [TimeTableController::class, 'deleteTimeTableEntry'])
    ->name('timetable-entries.destroy');

// Get details of a specific timetable entry
Route::middleware(['permission:schoolAdmin.specialty.timetable.show'])->get('/timetable-entries/{entryId}', [TimeTableController::class, 'getTimetableDetails'])
    ->name('timetable-entries.show');

// Get instructor availability for a specific semester and specialty
Route::middleware(['permission:schoolAdmin.specialty.timetable.avialability.view'])->get('/semesters/{semesterId}/specialties/{specialtyId}/instructor-availability', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty'])
    ->name('semesters.specialties.instructor-availability.index');
