<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradesCategoryController;

// Create a new grade category
Route::middleware(['permission:appAdmin.gradesCategory.create'])->post('/', [GradesCategoryController::class, 'createCategory'])
    ->name('grade-categories.store');

// Delete a specific grade category
Route::middleware(['permission:appAdmin.gradesCategory.delete'])->delete('/{categoryId}', [GradesCategoryController::class, 'deleteCategory'])
    ->name('grade-categories.destroy');

// Update a specific grade category
Route::middleware(['permission:appAdmin.gradesCategory.update'])->put('/{categoryId}', [GradesCategoryController::class, 'updateCategory'])
    ->name('grade-categories.update');

// Get all grade categories
Route::middleware(['permission:appAdmin.gradesCategory.view'])->get('/', [GradesCategoryController::class, 'getGradesCategory'])
    ->name('grade-categories.index');
