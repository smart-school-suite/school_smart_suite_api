<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesCategorycontroller;

// Create a new expense category
Route::post('/expense-categories', [ExpensesCategoryController::class, 'createCategory'])
    ->name('expense-categories.store');

// Get all expense categories
Route::get('/expense-categories', [ExpensesCategoryController::class, 'getCategory'])
    ->name('expense-categories.index');

// Update a specific expense category
Route::put('/expense-categories/{categoryId}', [ExpensesCategoryController::class, 'updateCategory'])
    ->name('expense-categories.update');

// Delete a specific expense category
Route::delete('/expense-categories/{categoryId}', [ExpensesCategoryController::class, 'deleteCategory'])
    ->name('expense-categories.destroy');
