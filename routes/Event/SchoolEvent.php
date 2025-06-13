<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;

// Create a new school event
Route::middleware(['permission:schoolAdmin.event.create'])->post('/create', [EventsController::class, 'createSchoolEvent'])
    ->name('school-event.store');

Route::get('/', [EventsController::class, 'getSchoolEvents'])->name("school-event.index");
Route::patch('/update/content/{eventId}', [EventsController::class, 'updateSchoolEventContent'])->name("school-event.update");
Route::get('/{categoryId}', [EventsController::class, 'getSchoolEventByCategory'])->name("school-event.category");
Route::delete('/delete/{eventId}', [EventsController::class, 'deleteSchoolEvent'])->name("school-event.delete");
Route::get('/details/{eventId}', [EventsController::class, 'getSchoolEventDetails'])->name("school-event.details");
Route::get('/status/{status}', [EventsController::class, 'getSchoolEventByStatus'])->name('school-event.status');
Route::patch('/draft/status/update/{eventId}', [EventsController::class, 'updateSchoolEventStatus'])->name('school-event.draft.status.update');
