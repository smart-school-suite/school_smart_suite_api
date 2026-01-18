<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TuitionFee\TuitionFeePaymentController;
use App\Http\Controllers\TuitionFee\TuitionFeeController;
use App\Http\Controllers\TuitionFee\TuitionFeeTransactionController;

    Route::middleware(['permission:schoolAdmin.tuitionFee.pay'])->post('/pay', [TuitionFeePaymentController::class, 'payTuitionFees'])
        ->name('tuition-fee-payments.store');

    Route::middleware(['permission:schoolAdmin.tuitionFee.view.paid'])->get('/paid', [TuitionFeeController::class, 'getFeesPaid'])
        ->name('tuition-fee-payments.index');

    Route::middleware(['permission:schoolAdmin.tuitionFee.view'])->get('/', [TuitionFeeController::class, 'getTuitionFees'])
        ->name('tuition-fees.index');

    Route::middleware(['permission:schoolAdmin.tuitionFee.delete'])->delete('/{feeId}', [TuitionFeeController::class, 'deleteFeePaid'])
        ->name('tuition-fee-payments.destroy');

    Route::middleware(['permission:schoolAdmin.tuitionFee.view.deptors'])->get('/debtors', [TuitionFeeController::class, 'getFeeDebtors'])
        ->name('tuition-fee-debtors.index');

    Route::middleware(['permission:schoolAdmin.tuitionFee.view.transactions'])->get('/transaction', [TuitionFeeTransactionController::class, 'getTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.index');

    Route::middleware(['permission:schoolAdmin.tuitionFee.show.transaction'])->get('/transaction/{transactionId}', [TuitionFeeTransactionController::class, 'getTuitionTransactionFeeDetails'])
        ->name('tuition-fee-transactions.show');

    Route::middleware(['permission:schoolAdmin.tuitionFee.reverse.transaction'])->delete('/transaction/{transactionId}/reverse', [TuitionFeeTransactionController::class, 'reverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.reverse');

    Route::middleware(['permission:schoolAdmin.tuitionFee.delete.transaction'])->delete('/transaction/{transactionId}', [TuitionFeeTransactionController::class, 'deleteTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.destroy');

    Route::middleware(['permission:schoolAdmin.tuitionFee.delete.transaction'])->post('/transaction/bulk-delete', [TuitionFeeTransactionController::class, 'bulkDeleteTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.bulk-delete');

    Route::middleware(['permission:schoolAdmin.tuitionFee.reverse.transaction'])->post('/transaction/bulk-reverse', [TuitionFeeTransactionController::class, 'bulkReverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.bulk-reverse');

    Route::get('/student/transactions', [TuitionFeePaymentController::class, "getStudentFinancialTransactions"])->name("get.student.financial.transactions");

