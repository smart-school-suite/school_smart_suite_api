<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolEvent\SchoolEventController;


Route::post('/create', [SchoolEventController::class, 'createSchoolEvent'])
    ->name('school-event.store');
Route::get('/', [SchoolEventController::class, 'getSchoolEvents'])->name("get.school-events");
Route::post('/{schoolEventId}/like', [SchoolEventController::class, 'likeSchoolEvent'])->name("like.school.event");
Route::get('/event-category/{eventCategoryId}', [SchoolEventController::class, 'getSchoolEventByCategory'])->name('get.school.events.by.category');
Route::get('/expired', [SchoolEventController::class, 'getExpiredSchoolEvents'])->name("get.expired.school-events");
Route::patch('/update/content/{schoolEventId}', [SchoolEventController::class, 'updateSchoolEventContent'])->name("school-event.update");
Route::delete('/{eventCategoryId}', [SchoolEventController::class, 'deleteSchoolEventContent'])->name("school-event.delete");
Route::patch('/draft/update', [SchoolEventController::class, 'updateDraftSchoolEvent'])->name('school-event.draft.status.update');
Route::get('details/{eventCategoryId}', [SchoolEventController::class, 'getSchoolEventDetails'])->name("school-event.details");
Route::get('/expired/event-category/{eventCategoryId}', [SchoolEventController::class, 'getExpiredSchoolEventsByCategory'])->name("get.expired.school.events.by.category");
Route::get('/scheduled/event-category/{eventCategoryId}', [SchoolEventController::class, 'getScheduledSchoolEventsByCategory'])->name("get.scheduled.school.events.by.category");
Route::get('/scheduled', [SchoolEventController::class, 'getScheduledSchoolEvents'])->name("get.scheduled.school.events");
Route::get('/draft', [SchoolEventController::class, 'getDraftSchoolEvents'])->name("get.draft.school-events");
Route::get('/draft/event-category/{eventCategoryId}', [SchoolEventController::class, 'getDraftSchoolEventsByCategory'])->name("get.draft.school.events.by.category");
Route::get('/student/event-upcoming', [SchoolEventController::class, 'getStudentUpcomingSchoolEvents'])->name("get.upcoming.school.events");
