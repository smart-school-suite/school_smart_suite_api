<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesController;

// Create a new expense
Route::middleware(['permission:schoolAdmin.schoolExpenses.create'])->post('/', [ExpensesController::class, 'createExpense'])
    ->name('expenses.store');

// Get all expenses for the authenticated user
Route::middleware(['permission:schoolAdmin.schoolExpenses.view'])->get('/', [ExpensesController::class, 'getExpenses'])
    ->name('expenses.index');

// Get details of a specific expense
Route::middleware(['permission:schoolAdmin.schoolExpenses.show'])->get('/{expenseId}', [ExpensesController::class, 'getExpensesDetails'])
    ->name('expenses.show');

// Update a specific expense
Route::middleware(['permission:schoolAdmin.schoolExpenses.update'])->put('/{expenseId}', [ExpensesController::class, 'updateExpense'])
    ->name('expenses.update');

// Delete a specific expense
Route::middleware(['permission:schoolAdmin.schoolExpenses.delete'])->delete('/{expenseId}', [ExpensesController::class, 'deleteExpense'])
    ->name('expenses.destroy');

// Bulk delete school expenses
Route::middleware(['permission:schoolAdmin.schoolExpenses.delete'])->post('/bulk-delete', [ExpensesController::class, 'bulkDeleteSchoolExpenses'])
    ->name('expenses.bulk-delete');

// Bulk update school expenses
Route::middleware(['permission:schoolAdmin.schoolExpenses.update'])->patch('/bulk-update', [ExpensesController::class, 'bulkUpdateSchoolExpenses'])
    ->name('expenses.bulk-update');
