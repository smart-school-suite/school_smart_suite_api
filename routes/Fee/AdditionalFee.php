<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAdditionalFeesController;

// Create a new student additional fee
Route::post('/student-additional-fees', [StudentAdditionalFeesController::class, 'createStudentAdditionalFees'])
    ->name('student-additional-fees.store');

// Get all student additional fees
Route::get('/student-additional-fees', [StudentAdditionalFeesController::class, 'getAdditionalFees'])
    ->name('student-additional-fees.index');

// Get additional fees for a specific student
Route::get('/students/{studentId}/additional-fees', [StudentAdditionalFeesController::class, 'getStudentAdditionalFees'])
    ->name('students.additional-fees.index');

// Update a specific student additional fee
Route::put('/student-additional-fees/{feeId}', [StudentAdditionalFeesController::class, 'updateStudentAdditionalFees'])
    ->name('student-additional-fees.update');

// Delete a specific student additional fee
Route::delete('/student-additional-fees/{feeId}', [StudentAdditionalFeesController::class, 'deleteStudentAdditionalFees'])
    ->name('student-additional-fees.destroy');

// Bulk delete student additional fees
Route::delete('/student-additional-fees/bulk-delete', [StudentAdditionalFeesController::class, 'bulkDeleteStudentAdditionalFees'])
    ->name('student-additional-fees.bulk-delete');

// Bill multiple students for additional fees
Route::post('/student-additional-fees/bulk-bill', [StudentAdditionalFeesController::class, 'bulkBillStudents'])
    ->name('student-additional-fees.bulk-bill');

// Pay additional fees for a student
Route::post('/student-additional-fees/pay', [StudentAdditionalFeesController::class, 'payAdditionalFees'])
    ->name('student-additional-fees.pay');

// Bulk pay additional fees for multiple students
Route::post('/student-additional-fees/bulk-pay', [StudentAdditionalFeesController::class, 'bulkPayFees'])
    ->name('student-additional-fees.bulk-pay');

// Get all additional fee transactions
Route::get('/additional-fee-transactions', [StudentAdditionalFeesController::class, 'getAdditionalFeesTransactions'])
    ->name('additional-fee-transactions.index');

// Get details of a specific additional fee transaction
Route::get('/additional-fee-transactions/{transactionId}', [StudentAdditionalFeesController::class, 'getTransactionDetails'])
    ->name('additional-fee-transactions.show');

// Delete a specific additional fee transaction
Route::delete('/additional-fee-transactions/{transactionId}', [StudentAdditionalFeesController::class, 'deleteTransaction'])
    ->name('additional-fee-transactions.destroy');

// Reverse a specific additional fee transaction
Route::delete('/additional-fee-transactions/{transactionId}/reverse', [StudentAdditionalFeesController::class, 'reverseAdditionalFeesTransaction'])
    ->name('additional-fee-transactions.reverse');

// Bulk reverse additional fee transactions
Route::post('/additional-fee-transactions/bulk-reverse/{transactionIds}', [StudentAdditionalFeesController::class, 'bulkReverseTransaction'])
    ->name('additional-fee-transactions.bulk-reverse');

// Bulk delete additional fee transactions
Route::delete('/additional-fee-transactions/bulk-delete/{transactionIds}', [StudentAdditionalFeesController::class, 'bulkDeleteTransaction'])
    ->name('additional-fee-transactions.bulk-delete');
