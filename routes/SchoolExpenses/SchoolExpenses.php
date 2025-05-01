<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesController;

Route::middleware(['auth:sanctum'])->post('/create-expenses', [ExpensesController::class, 'createExpense']);
Route::middleware(['auth:sanctum'])->delete('/delete-expenses/{expense_id}', [ExpensesController::class, 'deleteExpense']);
Route::middleware(['auth:sanctum'])->get('/my-expenses', [ExpensesController::class, 'getExpenses']);
Route::middleware(['auth:sanctum'])->get('/expenses-details/{expense_id}', [ExpensesController::class, 'getExpensesDetails']);
Route::middleware(['auth:sanctum'])->put('/update-expenses/{expense_id}', [ExpensesController::class, 'updateExpense']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteSchoolExpenses/{schoolExpensesIds}', [ExpensesController::class, 'bulkDeleteSchoolExpenses']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateSchoolExpenses', [ExpensesController::class, 'bulkUpdateSchoolExpenses']);
