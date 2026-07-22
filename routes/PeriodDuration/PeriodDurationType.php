<?php

use App\Http\Controllers\PeriodDuration\PeriodDurationTypeController;
use Illuminate\Support\Facades\Route;


Route::post('/create', [PeriodDurationTypeController::class, 'createPeriodDurationType'])->name('periodDurationType.create');
Route::put('{typeId}/update', [PeriodDurationTypeController::class, 'updateDurationType'])->name('periodDurationType.update');
Route::delete('{typeId}/delete', [PeriodDurationTypeController::class, 'deletePeriodDurationType'])->name('periodDurationType.delete');
Route::post('{typeId}/activate', [PeriodDurationTypeController::class, 'activatePeriodDuration'])->name('periodDurationType.activate');
Route::post('{typeId}/deactivate', [PeriodDurationTypeController::class, 'deactivatePeriodDuration'])->name('periodDurationType.deactivate');
Route::get('/', [PeriodDurationTypeController::class, 'getPeriodDurationTypes'])->name('periodDurationType.all');
Route::post('{typeId}/', [PeriodDurationTypeController::class, 'getPeriodDurationTypeById'])->name('periodDurationType.getById');
