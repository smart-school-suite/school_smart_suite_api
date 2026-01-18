<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdditionalFee\AdditionalFeeController;
use App\Http\Controllers\AdditionalFee\AdditionalFeePaymentController;


Route::post('/', [AdditionalFeeController::class, 'createStudentAdditionalFees'])
    ->name('student-additional-fees.store');
Route::get('/details/{feeId}', [AdditionalFeeController::class, "getAdditionalFeeDetails"])
     ->name('additional-fee-details');

Route::get('/', [AdditionalFeeController::class, 'getAdditionalFees'])
    ->name('student-additional-fees.index');

Route::get('/students/{studentId}/additional-fees', [AdditionalFeeController::class, 'getStudentAdditionalFeesStudentId'])
    ->name('students.additional-fees.index');

Route::put('/{feeId}', [AdditionalFeeController::class, 'updateStudentAdditionalFees'])
    ->name('student-additional-fees.update');

Route::delete('/{feeId}', [AdditionalFeeController::class, 'deleteStudentAdditionalFees'])
    ->name('student-additional-fees.destroy');

Route::post('/bulk-delete', [AdditionalFeeController::class, 'bulkDeleteStudentAdditionalFees'])
    ->name('student-additional-fees.bulk-delete');

Route::post('/bulk-bill', [AdditionalFeeController::class, 'bulkBillStudents'])
    ->name('student-additional-fees.bulk-bill');

Route::post('/pay', [AdditionalFeePaymentController::class, 'payAdditionalFees'])
    ->name('student-additional-fees.pay');

Route::post('/bulk-pay', [AdditionalFeePaymentController::class, 'bulkPayFees'])
    ->name('student-additional-fees.bulk-pay');

Route::get('/additional-fee-transactions', [AdditionalFeePaymentController::class, 'getAdditionalFeesTransactions'])
    ->name('additional-fee-transactions.index');

Route::get('/additional-fee-transactions/{transactionId}', [AdditionalFeePaymentController::class, 'getTransactionDetails'])
    ->name('additional-fee-transactions.show');

Route::delete('/additional-fee-transactions/{transactionId}', [AdditionalFeePaymentController::class, 'deleteTransaction'])
    ->name('additional-fee-transactions.destroy');

Route::delete('/additional-fee-transactions/{transactionId}/reverse', [AdditionalFeePaymentController::class, 'reverseAdditionalFeesTransaction'])
    ->name('additional-fee-transactions.reverse');

Route::post('/additional-fee-transactions/bulk-reverse', [AdditionalFeePaymentController::class, 'bulkReverseTransaction'])
    ->name('additional-fee-transactions.bulk-reverse');


Route::post('/additional-fee-transactions/bulk-delete', [AdditionalFeePaymentController::class, 'bulkDeleteTransaction'])
    ->name('additional-fee-transactions.bulk-delete');

Route::patch('/bulk-update', [AdditionalFeeController::class, "bulkUpdateAdditionalFee"])->name('bulk.update.additional.fee');
Route::get('/student/additional-fees/status/{status}', [AdditionalFeeController::class, "getStudentAdditionalFees"])->name("get.student.additionalfee");
