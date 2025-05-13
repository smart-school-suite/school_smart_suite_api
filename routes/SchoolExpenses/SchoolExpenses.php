<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesController;

// Create a new expense
Route::post('/expenses', [ExpensesController::class, 'createExpense'])
    ->name('expenses.store');

// Get all expenses for the authenticated user
Route::get('/expenses', [ExpensesController::class, 'getExpenses'])
    ->name('expenses.index');

// Get details of a specific expense
Route::get('/expenses/{expenseId}', [ExpensesController::class, 'getExpensesDetails'])
    ->name('expenses.show');

// Update a specific expense
Route::put('/expenses/{expenseId}', [ExpensesController::class, 'updateExpense'])
    ->name('expenses.update');

// Delete a specific expense
Route::delete('/expenses/{expenseId}', [ExpensesController::class, 'deleteExpense'])
    ->name('expenses.destroy');

// Bulk delete school expenses
Route::delete('/expenses/bulk-delete/{expensesIds}', [ExpensesController::class, 'bulkDeleteSchoolExpenses'])
    ->name('expenses.bulk-delete');

// Bulk update school expenses
Route::put('/expenses/bulk-update', [ExpensesController::class, 'bulkUpdateSchoolExpenses'])
    ->name('expenses.bulk-update');
