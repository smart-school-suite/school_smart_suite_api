<?php

use App\Http\Controllers\RegistrationFee\RegistrationFeeController;
use App\Http\Controllers\RegistrationFee\RegistrationFeePaymentController;
use App\Http\Controllers\RegistrationFee\RegistrationFeeTransactionController;
use Illuminate\Support\Facades\Route;

    // Pay registration fee for a student
    Route::post('/pay', [RegistrationFeePaymentController::class, 'payRegistrationFee'])
        ->name('registration-fee-payments.store');

    // Get all registration fees
    Route::get('/', [RegistrationFeeController::class, 'getRegistrationFees'])
        ->name('registration-fees.index');

    // Get all registration fee transactions
    Route::get('/transactions', [RegistrationFeeTransactionController::class, 'getRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.index');

    // Reverse a specific registration fee transaction
    Route::delete('/transation/{transactionId}/reverse', [RegistrationFeeTransactionController::class, 'reverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.reverse');

    // Bulk delete registration fee transactions
    Route::post('/transaction/bulk-delete', [RegistrationFeeTransactionController::class, 'bulkDeleteRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.bulk-delete');

    // Bulk reverse registration fee transactions
    Route::post('/transaction/bulk-reverse', [RegistrationFeeTransactionController::class, 'bulkReverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.bulk-reverse');

    // Bulk pay registration fees for multiple students
    Route::post('/bulk-pay', [RegistrationFeePaymentController::class, 'bulkPayRegistrationFee'])
        ->name('registration-fee-payments.bulk-pay');

    Route::get('/{feeId}', [RegistrationFeeController::class, 'registrationFeeDetails'])->name('registrationFee.details');

    Route::get('/transaction/{transactionId}', [RegistrationFeeTransactionController::class, 'getRegistrationFeeTransactionDetails'])->name('registrationFee.Transaction.details');

    Route::delete('/transaction/{delete}', [RegistrationFeeTransactionController::class, "deleteRegistrationFeeTransaction"])->name('delete.registration.fee.transaction');

    Route::post('/bulk-delete', [RegistrationFeeController::class, 'bulkDeleteRegistrationFee'])->name('bulk.delete.registration.fee');

    Route::delete('/{feeId}', [RegistrationFeeController::class, 'deleteRegistrationFee'])->name('delete.registration.fee');

    Route::get('/student/registration-fees', [RegistrationFeeController::class, 'getStudentRegistrationFee'])->name("get.student.registration.fees");

