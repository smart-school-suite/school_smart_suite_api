<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolExpense\SchoolExpenseController;


Route::post('/', [SchoolExpenseController::class, 'createExpense'])
    ->name('expenses.store');

Route::get('/', [SchoolExpenseController::class, 'getExpenses'])
    ->name('expenses.index');

Route::get('/{expenseId}', [SchoolExpenseController::class, 'getExpensesDetails'])
    ->name('expenses.show');

Route::put('/{expenseId}', [SchoolExpenseController::class, 'updateExpense'])
    ->name('expenses.update');

Route::delete('/{expenseId}', [SchoolExpenseController::class, 'deleteExpense'])
    ->name('expenses.destroy');

Route::post('/bulk-delete', [SchoolExpenseController::class, 'bulkDeleteSchoolExpenses'])
    ->name('expenses.bulk-delete');

Route::patch('/bulk-update', [SchoolExpenseController::class, 'bulkUpdateSchoolExpenses'])
    ->name('expenses.bulk-update');
