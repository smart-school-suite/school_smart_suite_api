<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;

Route::middleware(['auth:sanctum'])->post('/create-event', [EventsController::class, 'createEvent']);
Route::middleware(['auth:sanctum'])->put('/update-event/{event_id}', [EventsController::class, 'updateEvent']);
Route::middleware(['auth:sanctum'])->delete('/delete-event/{event_id}', [EventsController::class, 'deleteEvent']);
Route::middleware(['auth:sanctum'])->get('/school-events', [EventsController::class, 'getEvents']);
Route::middleware(['auth:sanctum'])->get("/school-event/details/{event_id}", [EventsController::class, "getEventDetails"]);
