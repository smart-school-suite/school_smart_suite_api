<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesCategorycontroller;

// Create a new expense category
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.create'])->post('/', [ExpensesCategoryController::class, 'createCategory'])
    ->name('expense-categories.store');

// Get all expense categories
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.view'])->get('/', [ExpensesCategoryController::class, 'getCategory'])
    ->name('expense-categories.index');

// Update a specific expense category
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.update'])->put('/{categoryId}', [ExpensesCategoryController::class, 'updateCategory'])
    ->name('expense-categories.update');

// Delete a specific expense category
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.delete'])->delete('/{categoryId}', [ExpensesCategoryController::class, 'deleteCategory'])
    ->name('expense-categories.destroy');
