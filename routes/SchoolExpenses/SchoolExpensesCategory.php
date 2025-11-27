<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesCategorycontroller;
use App\Http\Controllers\SchoolExpense\SchoolExpenseCategoryController;

// Create a new expense category
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.create'])->post('/', [SchoolExpenseCategoryController::class, 'createCategory'])
    ->name('expense-categories.store');

// Get all expense categories
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.view'])->get('/', [SchoolExpenseCategoryController::class, 'getCategory'])
    ->name('expense-categories.index');

// Update a specific expense category
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.update'])->put('/{categoryId}', [SchoolExpenseCategoryController::class, 'updateCategory'])
    ->name('expense-categories.update');

// Delete a specific expense category
Route::middleware(['permission:schoolAdmin.schoolExpenses.category.delete'])->delete('/{categoryId}', [SchoolExpenseCategoryController::class, 'deleteCategory'])
    ->name('expense-categories.destroy');
