<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventTagController;

Route::post('/', [EventTagController::class, 'createEventTag'])->name('event-tag.create');
Route::get('/', [EventTagController::class, 'getEventTag'])->name('event-tag.index');
Route::put('/{tagId}', [EventTagController::class, 'updateEventTag'])->name('event-tag.update');
Route::delete('/{tagId}', [EventTagController::class, 'deleteEventTag'])->name('event-tag.delete');
