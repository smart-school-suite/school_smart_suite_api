<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolExpense\SchoolExpenseCategoryController;

Route::post('/', [SchoolExpenseCategoryController::class, 'createCategory'])
    ->name('expense-categories.store');

Route::get('/', [SchoolExpenseCategoryController::class, 'getCategory'])
    ->name('expense-categories.index');

Route::put('/{categoryId}', [SchoolExpenseCategoryController::class, 'updateCategory'])
    ->name('expense-categories.update');

Route::delete('/{categoryId}', [SchoolExpenseCategoryController::class, 'deleteCategory'])
    ->name('expense-categories.destroy');
