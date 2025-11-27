<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\TuitionFee\TuitionFeePaymentController;
use App\Http\Controllers\TuitionFee\TuitionFeeController;
use App\Http\Controllers\TuitionFee\TuitionFeeTransactionController;
// Pay tuition fees for a student
    Route::middleware(['permission:schoolAdmin.tuitionFee.pay'])->post('/tuition-fee-payments', [TuitionFeePaymentController::class, 'payTuitionFees'])
        ->name('tuition-fee-payments.store');

    // Get all paid tuition fees
    Route::middleware(['permission:schoolAdmin.tuitionFee.view.paid'])->get('/tuition-fee-payments', [TuitionFeeController::class, 'getFeesPaid'])
        ->name('tuition-fee-payments.index');

    // Get all tuition fees
    Route::middleware(['permission:schoolAdmin.tuitionFee.view'])->get('/tuition-fees', [TuitionFeeController::class, 'getTuitionFees'])
        ->name('tuition-fees.index');

    // Update a specific tuition fee payment record
    Route::middleware(['permission:schoolAdmin.tuitionFee.update'])->put('/tuition-fee-payments/{feeId}', [TuitionFeeController::class, 'updateFeesPaid'])
        ->name('tuition-fee-payments.update');

    // Delete a specific tuition fee payment record
    Route::middleware(['permission:schoolAdmin.tuitionFee.delete'])->delete('/tuition-fee-payments/{feeId}', [TuitionFeeController::class, 'deleteFeePaid'])
        ->name('tuition-fee-payments.destroy');

    // Get all students with outstanding tuition fees
    Route::middleware(['permission:schoolAdmin.tuitionFee.view.deptors'])->get('/tuition-fee-debtors', [TuitionFeeController::class, 'getFeeDebtors'])
        ->name('tuition-fee-debtors.index');

    // Get all tuition fee transactions
    Route::middleware(['permission:schoolAdmin.tuitionFee.view.transactions'])->get('/tuition-fee-transactions', [TuitionFeePaymentController::class, 'getTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.index');

    // Get details of a specific tuition fee transaction
    Route::middleware(['permission:schoolAdmin.tuitionFee.show.transaction'])->get('/tuition-fee-transactions/{transactionId}', [TuitionFeeTransactionController::class, 'getTuitionTransactionFeeDetails'])
        ->name('tuition-fee-transactions.show');

    // Reverse a specific tuition fee transaction
    Route::middleware(['permission:schoolAdmin.tuitionFee.reverse.transaction'])->delete('/tuition-fee-transactions/{transactionId}/reverse', [TuitionFeeTransactionController::class, 'reverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.reverse');

    // Delete a specific tuition fee transaction
    Route::middleware(['permission:schoolAdmin.tuitionFee.delete.transaction'])->delete('/tuition-fee-transactions/{transactionId}', [TuitionFeeTransactionController::class, 'deleteTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.destroy');

    // Bulk delete tuition fee transactions
    Route::middleware(['permission:schoolAdmin.tuitionFee.delete.transaction'])->post('/tuition-fee-transactions/bulk-delete', [TuitionFeeTransactionController::class, 'bulkDeleteTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.bulk-delete');

    // Bulk reverse tuition fee transactions
    Route::middleware(['permission:schoolAdmin.tuitionFee.reverse.transaction'])->post('/tuition-fee-transactions/bulk-reverse', [TuitionFeeTransactionController::class, 'bulkReverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.bulk-reverse');

    Route::get('/student/transactions', [TuitionFeePaymentController::class, "getStudentFinancialTransactions"])->name("get.student.financial.transactions");

