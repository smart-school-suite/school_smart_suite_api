<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;

// Create a new school event
Route::middleware(['permission:schoolAdmin.event.create'])->post('/school-events/create', [EventsController::class, 'createSchoolEvent'])
    ->name('school-events.store');

