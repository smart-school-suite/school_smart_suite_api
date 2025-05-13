<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradesCategoryController;

// Create a new grade category
Route::post('/grade-categories', [GradesCategoryController::class, 'createCategory'])
    ->name('grade-categories.store');

// Delete a specific grade category
Route::delete('/grade-categories/{categoryId}', [GradesCategoryController::class, 'deleteCategory'])
    ->name('grade-categories.destroy');

// Update a specific grade category
Route::put('/grade-categories/{categoryId}', [GradesCategoryController::class, 'updateCategory'])
    ->name('grade-categories.update');

// Get all grade categories
Route::get('/grade-categories', [GradesCategoryController::class, 'getGradesCategory'])
    ->name('grade-categories.index');
