<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Level\LevelController;

// Create a new education level
Route::middleware(['permission:appAdmin.level.create'])->post('/', [LevelController::class, 'createEducationLevel'])
    ->name('education-levels.store');

// Get all education levels
Route::get('/', [LevelController::class, 'getEducationLevel'])
    ->name('education-levels.index');

// Update a specific education level
Route::middleware(['permission:appAdmin.level.update'])->put('/{levelId}', [LevelController::class, 'updateEducationLevel'])
    ->name('education-levels.update');

// Delete a specific education level
Route::middleware(['permission:appAdmin.level.delete'])->delete('/{levelId}', [LevelController::class, 'deleteEducationLevel'])
    ->name('education-levels.destroy');
