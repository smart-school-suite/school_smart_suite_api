<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorAvailabilityController;

Route::middleware(['auth:sanctum'])->post('/create-availability', [InstructorAvailabilityController::class, 'createInstructorAvailability']);
Route::middleware(['auth:sanctum'])->delete('/delete-availability/{availabilty_id}', [InstructorAvailabilityController::class, 'deleteInstructorAvailabilty']);
Route::middleware(['auth:sanctum'])->put('/update-availability/{availability_id}', [InstructorAvailabilityController::class, 'updateInstructorAvailability']);
Route::middleware(['auth:sanctum'])->get('/teacher-avialability/{teacher_id}', [InstructorAvailabilityController::class, 'getInstructorAvailability']);
