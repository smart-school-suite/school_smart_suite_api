<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeePaymentController;

// Pay tuition fees for a student
    Route::middleware(['permission:schoolAdmin.tuitionFee.pay'])->post('/tuition-fee-payments', [FeePaymentController::class, 'payTuitionFees'])
        ->name('tuition-fee-payments.store');

    // Get all paid tuition fees
    Route::middleware(['permission:schoolAdmin.tuitionFee.view.paid'])->get('/tuition-fee-payments', [FeePaymentController::class, 'getFeesPaid'])
        ->name('tuition-fee-payments.index');

    // Get all tuition fees
    Route::middleware(['permission:schoolAdmin.tuitionFee.view'])->get('/tuition-fees', [FeePaymentController::class, 'getTuitionFees'])
        ->name('tuition-fees.index');

    // Update a specific tuition fee payment record
    Route::middleware(['permission:schoolAdmin.tuitionFee.update'])->put('/tuition-fee-payments/{feeId}', [FeePaymentController::class, 'updateFeesPaid'])
        ->name('tuition-fee-payments.update');

    // Delete a specific tuition fee payment record
    Route::middleware(['permission:schoolAdmin.tuitionFee.delete'])->delete('/tuition-fee-payments/{feeId}', [FeePaymentController::class, 'deleteFeePaid'])
        ->name('tuition-fee-payments.destroy');

    // Get all students with outstanding tuition fees
    Route::middleware(['permission:schoolAdmin.tuitionFee.view.deptors'])->get('/tuition-fee-debtors', [FeePaymentController::class, 'getFeeDebtors'])
        ->name('tuition-fee-debtors.index');

    // Get all tuition fee transactions
    Route::middleware(['permission:schoolAdmin.tuitionFee.view.transactions'])->get('/tuition-fee-transactions', [FeePaymentController::class, 'getTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.index');

    // Get details of a specific tuition fee transaction
    Route::middleware(['permission:schoolAdmin.tuitionFee.show.transaction'])->get('/tuition-fee-transactions/{transactionId}', [FeePaymentController::class, 'getTuitionTransactionFeeDetails'])
        ->name('tuition-fee-transactions.show');

    // Reverse a specific tuition fee transaction
    Route::middleware(['permission:schoolAdmin.tuitionFee.reverse.transaction'])->delete('/tuition-fee-transactions/{transactionId}/reverse', [FeePaymentController::class, 'reverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.reverse');

    // Delete a specific tuition fee transaction
    Route::middleware(['permission:schoolAdmin.tuitionFee.delete.transaction'])->delete('/tuition-fee-transactions/{transactionId}', [FeePaymentController::class, 'deleteTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.destroy');

    // Bulk delete tuition fee transactions
    Route::middleware(['permission:schoolAdmin.tuitionFee.delete.transaction'])->post('/tuition-fee-transactions/bulk-delete', [FeePaymentController::class, 'bulkDeleteTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.bulk-delete');

    // Bulk reverse tuition fee transactions
    Route::middleware(['permission:schoolAdmin.tuitionFee.reverse.transaction'])->post('/tuition-fee-transactions/bulk-reverse', [FeePaymentController::class, 'bulkReverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.bulk-reverse');

    // Pay registration fee for a student
    Route::middleware(['permission:schoolAdmin.registrationFee.pay'])->post('/registration-fee-payments', [FeePaymentController::class, 'payRegistrationFees'])
        ->name('registration-fee-payments.store');

    // Get all registration fees
    Route::middleware(['permission:schoolAdmin.registrationFee.view'])->get('/registration-fees', [FeePaymentController::class, 'getRegistrationFees'])
        ->name('registration-fees.index');

    // Get all registration fee transactions
    Route::middleware(['permission:schoolAdmin.registrationFee.view.transaction'])->get('/registration-fee-transactions', [FeePaymentController::class, 'getRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.index');

    // Reverse a specific registration fee transaction
    Route::middleware(['permission:schoolAdmin.registrationFee.reverse.transaction'])->delete('/registration-fee-transactions/{transactionId}/reverse', [FeePaymentController::class, 'reverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.reverse');

    // Bulk delete registration fee transactions
    Route::middleware(['permission:schoolAdmin.registrationFee.delete.transaction'])->post('/registration-fee-transactions/bulk-delete', [FeePaymentController::class, 'bulkDeleteRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.bulk-delete');

    // Bulk reverse registration fee transactions
    Route::middleware(['permission:schoolAdmin.registrationFee.reverse.transaction'])->post('/registration-fee-transactions/bulk-reverse', [FeePaymentController::class, 'bulkReverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.bulk-reverse');

    // Bulk pay registration fees for multiple students
    Route::middleware(['permission:schoolAdmin.registrationFee.pay'])->post('/registration-fee-payments/bulk-pay', [FeePaymentController::class, 'bulkPayRegistrationFee'])
        ->name('registration-fee-payments.bulk-pay');

    Route::get('/tuition-fee/{feeId}', [FeePaymentController::class, 'getTuitionFeeDetails'])->name('get.tuition.fee.details');

    Route::get('/registration-fee/transaction/{transactionId}', [FeePaymentController::class, 'getRegistrationFeeTransactionDetails'])->name('get.registration.fee.transaction.details');

    Route::delete('/registration-fee/transaction/{delete}', [FeePaymentController::class, "deleteRegistrationFeeTransaction"])->name('delete.registration.fee.transaction');

    Route::post('/registration-fee/bulk-delete', [FeePaymentController::class, 'bulkDeleteRegistrationFee'])->name('bulk.delete.registration.fee');

    Route::delete('/registration-fee/{feeId}', [FeePaymentController::class, 'deleteRegistrationFee'])->name('delete.registration.fee');
