<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemTimetableConstraint\ConstraintCategoryController;

Route::get('/', [ConstraintCategoryController::class, 'getConstraintCategories'])->name("getConstraintCategories");
Route::get('/{constraintCategoryId}', [ConstraintCategoryController::class, 'getConstraintCategoryById'])->name("getConstraintCategoryById");
Route::post('/{constraintCategoryId}/activate', [ConstraintCategoryController::class, 'activateConstraintCategory'])->name("activateConstraintCategory");
Route::post('/{constraintCategoryId}/deactivate', [ConstraintCategoryController::class, 'deactivateConstraintCategory'])->name("deactivateConstraintCategory");
