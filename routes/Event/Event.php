<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;

// Create a new school event
Route::post('/school-events', [EventsController::class, 'createEvent'])
    ->name('school-events.store');

// Get all school events
Route::get('/school-events', [EventsController::class, 'getEvents'])
    ->name('school-events.index');

// Get details of a specific school event
Route::get('/school-events/{eventId}', [EventsController::class, 'getEventDetails'])
    ->name('school-events.show');

// Update a specific school event
Route::put('/school-events/{eventId}', [EventsController::class, 'updateEvent'])
    ->name('school-events.update');

// Delete a specific school event
Route::delete('/school-events/{eventId}', [EventsController::class, 'deleteEvent'])
    ->name('school-events.destroy');
