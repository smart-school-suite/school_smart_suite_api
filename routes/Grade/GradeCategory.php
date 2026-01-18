<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradeScale\GradeScaleCategoryController;

Route::post('/', [GradeScaleCategoryController::class, 'createCategory'])
    ->name('grade-categories.store');

Route::delete('/{categoryId}', [GradeScaleCategoryController::class, 'deleteCategory'])
    ->name('grade-categories.destroy');

Route::put('/{categoryId}', [GradeScaleCategoryController::class, 'updateCategory'])
    ->name('grade-categories.update');

Route::get('/', [GradeScaleCategoryController::class, 'getGradesCategory'])
    ->name('grade-categories.index');
