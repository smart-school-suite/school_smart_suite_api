<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeePaymentController;

// Pay tuition fees for a student
    Route::post('/tuition-fee-payments', [FeePaymentController::class, 'payTuitionFees'])
        ->name('tuition-fee-payments.store');

    // Get all paid tuition fees
    Route::get('/tuition-fee-payments', [FeePaymentController::class, 'getFeesPaid'])
        ->name('tuition-fee-payments.index');

    // Get all tuition fees
    Route::get('/tuition-fees', [FeePaymentController::class, 'getTuitionFees'])
        ->name('tuition-fees.index');

    // Update a specific tuition fee payment record
    Route::put('/tuition-fee-payments/{feeId}', [FeePaymentController::class, 'updateFeesPaid'])
        ->name('tuition-fee-payments.update');

    // Delete a specific tuition fee payment record
    Route::delete('/tuition-fee-payments/{feeId}', [FeePaymentController::class, 'deleteFeePaid'])
        ->name('tuition-fee-payments.destroy');

    // Get all students with outstanding tuition fees
    Route::get('/tuition-fee-debtors', [FeePaymentController::class, 'getFeeDebtors'])
        ->name('tuition-fee-debtors.index');

    // Get all tuition fee transactions
    Route::get('/tuition-fee-transactions', [FeePaymentController::class, 'getTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.index');

    // Get details of a specific tuition fee transaction
    Route::get('/tuition-fee-transactions/{transactionId}', [FeePaymentController::class, 'getTuitionTransactionFeeDetails'])
        ->name('tuition-fee-transactions.show');

    // Reverse a specific tuition fee transaction
    Route::delete('/tuition-fee-transactions/{transactionId}/reverse', [FeePaymentController::class, 'reverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.reverse');

    // Delete a specific tuition fee transaction
    Route::delete('/tuition-fee-transactions/{transactionId}', [FeePaymentController::class, 'deleteTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.destroy');

    // Bulk delete tuition fee transactions
    Route::delete('/tuition-fee-transactions/bulk-delete/{transactionIds}', [FeePaymentController::class, 'bulkDeleteTuitionFeeTransactions'])
        ->name('tuition-fee-transactions.bulk-delete');

    // Bulk reverse tuition fee transactions
    Route::post('/tuition-fee-transactions/bulk-reverse/{transactionIds}', [FeePaymentController::class, 'bulkReverseTuitionFeeTransaction'])
        ->name('tuition-fee-transactions.bulk-reverse');

    // Pay registration fee for a student
    Route::post('/registration-fee-payments', [FeePaymentController::class, 'payRegistrationFees'])
        ->name('registration-fee-payments.store');

    // Get all registration fees
    Route::get('/registration-fees', [FeePaymentController::class, 'getRegistrationFees'])
        ->name('registration-fees.index');

    // Get all registration fee transactions
    Route::get('/registration-fee-transactions', [FeePaymentController::class, 'getRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.index');

    // Reverse a specific registration fee transaction
    Route::delete('/registration-fee-transactions/{transactionId}/reverse', [FeePaymentController::class, 'reverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.reverse');

    // Bulk delete registration fee transactions
    Route::delete('/registration-fee-transactions/bulk-delete/{transactionIds}', [FeePaymentController::class, 'bulkDeleteRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.bulk-delete');

    // Bulk reverse registration fee transactions
    Route::post('/registration-fee-transactions/bulk-reverse/{transactionIds}', [FeePaymentController::class, 'bulkReverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.bulk-reverse');

    // Bulk pay registration fees for multiple students
    Route::post('/registration-fee-payments/bulk-pay', [FeePaymentController::class, 'bulkPayRegistrationFee'])
        ->name('registration-fee-payments.bulk-pay');
