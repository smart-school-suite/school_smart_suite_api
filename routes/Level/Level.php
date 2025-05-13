<?php

use App\Http\Controllers\EducationLevelsController;
use Illuminate\Support\Facades\Route;

// Create a new education level
Route::post('/education-levels', [EducationLevelsController::class, 'createEducationLevel'])
    ->name('education-levels.store');

// Get all education levels
Route::get('/education-levels', [EducationLevelsController::class, 'getEducationLevel'])
    ->name('education-levels.index');

// Update a specific education level
Route::put('/education-levels/{levelId}', [EducationLevelsController::class, 'updateEducationLevel'])
    ->name('education-levels.update');

// Delete a specific education level
Route::delete('/education-levels/{levelId}', [EducationLevelsController::class, 'deleteEducationLevel'])
    ->name('education-levels.destroy');
