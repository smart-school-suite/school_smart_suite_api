<?php

use App\Http\Controllers\RegistrationFee\RegistrationFeeController;
use App\Http\Controllers\RegistrationFee\RegistrationFeePaymentController;
use App\Http\Controllers\RegistrationFee\RegistrationFeeTransactionController;
use Illuminate\Support\Facades\Route;

    // Pay registration fee for a student
    Route::middleware(['permission:schoolAdmin.registrationFee.pay'])->post('/registration-fee-payments', [RegistrationFeePaymentController::class, 'payRegistrationFees'])
        ->name('registration-fee-payments.store');

    // Get all registration fees
    Route::middleware(['permission:schoolAdmin.registrationFee.view'])->get('/registration-fees', [RegistrationFeePaymentController::class, 'getRegistrationFees'])
        ->name('registration-fees.index');

    // Get all registration fee transactions
    Route::middleware(['permission:schoolAdmin.registrationFee.view.transaction'])->get('/registration-fee-transactions', [RegistrationFeeTransactionController::class, 'getRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.index');

    // Reverse a specific registration fee transaction
    Route::middleware(['permission:schoolAdmin.registrationFee.reverse.transaction'])->delete('/registration-fee-transactions/{transactionId}/reverse', [RegistrationFeeTransactionController::class, 'reverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.reverse');

    // Bulk delete registration fee transactions
    Route::middleware(['permission:schoolAdmin.registrationFee.delete.transaction'])->post('/registration-fee-transactions/bulk-delete', [RegistrationFeeTransactionController::class, 'bulkDeleteRegistrationFeeTransactions'])
        ->name('registration-fee-transactions.bulk-delete');

    // Bulk reverse registration fee transactions
    Route::middleware(['permission:schoolAdmin.registrationFee.reverse.transaction'])->post('/registration-fee-transactions/bulk-reverse', [RegistrationFeeTransactionController::class, 'bulkReverseRegistrationFeeTransaction'])
        ->name('registration-fee-transactions.bulk-reverse');

    // Bulk pay registration fees for multiple students
    Route::middleware(['permission:schoolAdmin.registrationFee.pay'])->post('/registration-fee-payments/bulk-pay', [RegistrationFeePaymentController::class, 'bulkPayRegistrationFee'])
        ->name('registration-fee-payments.bulk-pay');

    Route::get('/tuition-fee/{feeId}', [RegistrationFeeController::class, 'getTuitionFeeDetails'])->name('get.tuition.fee.details');

    Route::get('/registration-fee/transaction/{transactionId}', [RegistrationFeeTransactionController::class, 'getRegistrationFeeTransactionDetails'])->name('get.registration.fee.transaction.details');

    Route::delete('/registration-fee/transaction/{delete}', [RegistrationFeeTransactionController::class, "deleteRegistrationFeeTransaction"])->name('delete.registration.fee.transaction');

    Route::post('/registration-fee/bulk-delete', [RegistrationFeeController::class, 'bulkDeleteRegistrationFee'])->name('bulk.delete.registration.fee');

    Route::delete('/registration-fee/{feeId}', [RegistrationFeeController::class, 'deleteRegistrationFee'])->name('delete.registration.fee');

    Route::get('/student/registration-fees', [RegistrationFeeController::class, 'getStudentRegistrationFee'])->name("get.student.registration.fees");

