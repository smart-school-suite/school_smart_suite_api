<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeTableController;

// Create a new timetable based on availability for a specific semester
Route::middleware(['permission:schoolAdmin.specialty.timetable.create'])->post('/semesters/timetable/availability', [TimeTableController::class, 'createTimetableByAvailability'])
    ->name('semesters.timetable.availability.store');

// Create a new timetable for a specific semester
Route::middleware(['permission:schoolAdmin.specialty.timetable.create'])->post('/semesters/timetable', [TimeTableController::class, 'createTimetable'])
    ->name('semesters.timetable.store');

// Update a specific timetable entry
Route::middleware(['permission:schoolAdmin.specialty.timetable.update'])->patch('/timetable/specialty', [TimeTableController::class, 'updateTimetable'])
    ->name('timetable.update');

// Update timetable based on teacher availability
Route::middleware(['permission:schoolAdmin.specialty.timetable.update'])->patch('/timetable/availability', [TimeTableController::class, 'updateTimetableByTeacherAvailability'])
    ->name('timetable.availability.update');

// Delete the entire timetable for a specific student batch, specialty, level, and semester
Route::middleware(['permission:schoolAdmin.specialty.timetable.delete'])->post('/timetable/specialty', [TimeTableController::class, 'deleteTimetable'])
    ->name('timetable.delete');

// Generate a timetable (requires request parameters to specify criteria)
Route::middleware(['permission:schoolAdmin.specialty.timetable.view'])->post('/timetable/generate', [TimeTableController::class, 'generateTimetable'])
    ->name('timetable.generate');

// Delete a specific timetable entry
Route::middleware(['permission:schoolAdmin.specialty.timetable.delete'])->delete('/timetable-entry/{entryId}', [TimeTableController::class, 'deleteTimeTableEntry'])
    ->name('timetable-entry.destroy');

// Get details of a specific timetable entry
Route::middleware(['permission:schoolAdmin.specialty.timetable.show'])->get('/timetable-entry/{entryId}', [TimeTableController::class, 'getTimetableDetails'])
    ->name('timetable-entry.show');

// Get instructor availability for a specific semester and specialty
Route::middleware(['permission:schoolAdmin.specialty.timetable.avialability.view'])->get('/semesters/{semesterId}/specialties/{specialtyId}/instructor-availability', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty'])
    ->name('semesters.specialties.instructor-availability.index');
