<?php

use App\Http\Controllers\EducationLevelsController;
use Illuminate\Support\Facades\Route;

// Create a new education level
Route::middleware(['permission:appAdmin.level.create'])->post('/', [EducationLevelsController::class, 'createEducationLevel'])
    ->name('education-levels.store');

// Get all education levels
Route::get('/', [EducationLevelsController::class, 'getEducationLevel'])
    ->name('education-levels.index');

// Update a specific education level
Route::middleware(['permission:appAdmin.level.update'])->put('/{levelId}', [EducationLevelsController::class, 'updateEducationLevel'])
    ->name('education-levels.update');

// Delete a specific education level
Route::middleware(['permission:appAdmin.level.delete'])->delete('/{levelId}', [EducationLevelsController::class, 'deleteEducationLevel'])
    ->name('education-levels.destroy');
