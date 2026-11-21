<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TuitionFee\TuitionFeePaymentController;
use App\Http\Controllers\TuitionFee\TuitionFeeController;
use App\Http\Controllers\TuitionFee\TuitionFeeTransactionController;

    Route::post('/pay', [TuitionFeePaymentController::class, 'payTuitionFees'])
        ->name('tuition-fee-payments.store');

    Route::get('/paid', [TuitionFeeController::class, 'getFeesPaid'])
        ->name('tuition-fee-payments.index');

    Route::get('/', [TuitionFeeController::class, 'getTuitionFees'])
        ->name('tuition-fees.index');

    Route::delete('/{feeId}', [TuitionFeeController::class, 'deleteFeePaid'])
        ->name('tuition-fee-payments.destroy');

    Route::get('/debtors', [TuitionFeeController::class, 'getFeeDebtors'])
        ->name('tuition-fee-debtors.index');

    Route::get('/transaction', [TuitionFeeTransactionController::class, 'getTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.index');

    Route::get('/transaction/{transactionId}', [TuitionFeeTransactionController::class, 'getTuitionTransactionFeeDetails'])
        ->name('tuition-fee-transactions.show');

    Route::delete('/transaction/{transactionId}/reverse', [TuitionFeeTransactionController::class, 'reverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.reverse');

    Route::delete('/transaction/{transactionId}', [TuitionFeeTransactionController::class, 'deleteTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.destroy');

    Route::post('/transaction/bulk-delete', [TuitionFeeTransactionController::class, 'bulkDeleteTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.bulk-delete');

    Route::post('/transaction/bulk-reverse', [TuitionFeeTransactionController::class, 'bulkReverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.bulk-reverse');

    Route::get('/student/transactions', [TuitionFeePaymentController::class, "getStudentFinancialTransactions"])->name("get.student.financial.transactions");

