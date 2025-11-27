<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradeScale\GradeScaleCategoryController;

// Create a new grade category
Route::middleware(['permission:appAdmin.gradesCategory.create'])->post('/', [GradeScaleCategoryController::class, 'createCategory'])
    ->name('grade-categories.store');

// Delete a specific grade category
Route::middleware(['permission:appAdmin.gradesCategory.delete'])->delete('/{categoryId}', [GradeScaleCategoryController::class, 'deleteCategory'])
    ->name('grade-categories.destroy');

// Update a specific grade category
Route::middleware(['permission:appAdmin.gradesCategory.update'])->put('/{categoryId}', [GradeScaleCategoryController::class, 'updateCategory'])
    ->name('grade-categories.update');

// Get all grade categories
Route::middleware(['permission:appAdmin.gradesCategory.view'])->get('/', [GradeScaleCategoryController::class, 'getGradesCategory'])
    ->name('grade-categories.index');
