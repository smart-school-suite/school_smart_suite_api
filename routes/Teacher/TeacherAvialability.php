<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorAvailabilityController;

// Create new instructor availability
Route::middleware(['permission:teacher.avialability.create'])->post('/instructor-availability', [InstructorAvailabilityController::class, 'createInstructorAvailability'])
    ->name('instructor-availability.store');

// Get availability for a specific teacher
Route::middleware(['permission:teacher.avialability.show.teacher'])->get('/teachers/{teacherId}/availability', [InstructorAvailabilityController::class, 'getInstructorAvailability'])
    ->name('teachers.availability.index');

// Update specific instructor availability
Route::middleware(['permission:teacher.avialability.update'])->put('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'updateInstructorAvailability'])
    ->name('instructor-availability.update');

// Delete specific instructor availability
Route::middleware(['permission:teacher.avialability.delete'])->delete('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'deleteInstructorAvailabilty'])
    ->name('instructor-availability.destroy');
