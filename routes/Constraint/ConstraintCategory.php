<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Constraint\ConstraintCategoryController;

Route::get('/', [ConstraintCategoryController::class, 'getAllConstraintCategories'])->name('constraintCategories.get');
Route::get('/{constraintCategoryId}/details', [ConstraintCategoryController::class, 'getConstraintCategoryById'])->name('constraintCategories.getById');
Route::post('/{constraintCategoryId}/deactivate', [ConstraintCategoryController::class, 'deactivateConstraintCategory'])->name('constraintCategories.deactivate');
Route::post('/{constraintCategoryId}/activate', [ConstraintCategoryController::class, 'activateConstraintCategory'])->name('constraintCategories.activate');
