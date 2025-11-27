<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolEvent\SchoolEventTagController;

Route::post('/', [SchoolEventTagController::class, 'createEventTag'])->name('event-tag.create');
Route::get('/', [SchoolEventTagController::class, 'getEventTag'])->name('event-tag.index');
Route::put('/{tagId}', [SchoolEventTagController::class, 'updateEventTag'])->name('event-tag.update');
Route::delete('/{tagId}', [SchoolEventTagController::class, 'deleteEventTag'])->name('event-tag.delete');
