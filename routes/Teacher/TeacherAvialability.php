<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorAvailabilityController;

// Create new instructor availability
Route::post('/instructor-availability', [InstructorAvailabilityController::class, 'createInstructorAvailability'])
    ->name('instructor-availability.store');

// Get availability for a specific teacher
Route::get('/teachers/{teacherId}/availability', [InstructorAvailabilityController::class, 'getInstructorAvailability'])
    ->name('teachers.availability.index');

// Update specific instructor availability
Route::put('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'updateInstructorAvailability'])
    ->name('instructor-availability.update');

// Delete specific instructor availability
Route::delete('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'deleteInstructorAvailabilty'])
    ->name('instructor-availability.destroy');
