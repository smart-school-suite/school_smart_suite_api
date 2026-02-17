<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableDraftController;

Route::post('/create', [SemesterTimetableDraftController::class, 'createTimetableDraft'])->name('semeterTimetable.draft.create');
Route::delete('/{schoolSemesterId}/drafts', [SemesterTimetableDraftController::class, 'deleteTimetableDraft'])->name('semeterTimetable.draft.delete');
Route::get('/{schoolSemesterId}/drafts', [SemesterTimetableDraftController::class, 'getTimetableDrafts'])->name('semeterTimetable.draft.list');
Route::get('/{schoolSemesterId}/drafts/versions', [SemesterTimetableDraftController::class, 'getTimetableDraftWithVersions'])->name('semeterTimetable.draft.versions');
