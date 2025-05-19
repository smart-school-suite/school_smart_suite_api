<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorAvailabilityController;

// Create new instructor availability
Route::middleware(['permission:teacher.avialability.create'])->post('/instructor-availability', [InstructorAvailabilityController::class, 'createInstructorAvailability'])
    ->name('instructor-availability.store');

// Get availability for a specific teacher
Route::middleware(['permission:teacher.avialability.show'])->get('/teachers/{teacherId}/availability', [InstructorAvailabilityController::class, 'getInstructorAvailability'])
    ->name('teachers.availability.index');

// Update specific instructor availability
Route::middleware(['permission:teacher.avialability.update'])->put('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'updateInstructorAvailability'])
    ->name('instructor-availability.update');

Route::middleware(['permission:teacher.avialability.update'])->patch('/instructor-availability', [InstructorAvailabilityController::class, 'bulkUpdateInstructorAvialabililty'])
       ->name('instructor-availability.bulk-update');
// Delete specific instructor availability
Route::delete('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'deleteInstructorAvailabilty'])
    ->name('instructor-availability.destroy');

Route::get('/school-semesters/teacher/{teacherId}/specialty-preference', [InstructorAvailabilityController::class, 'getSchoolSemestersByTeacherSpecialtyPreference']);

Route::delete('/instructor-availability/teacher/{teacherId}/school-semester/{schoolSemesterId}', [InstructorAvailabilityController::class, 'deleteAllAvailabilitySlotsBySemester'])
    ->name('instructor-availability.delete-all-by-teacher-semester');

