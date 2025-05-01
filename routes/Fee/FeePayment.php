<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeePaymentController;

Route::middleware(['auth:sanctum'])->post('/pay-fees', [FeePaymentController::class, 'payTuitionFees']);
Route::middleware(['auth:sanctum'])->get('/paid-fees', [FeePaymentController::class, 'getFeesPaid']);
Route::middleware(['auth:sanctum'])->put('/update-payment/{fee_id}', [FeePaymentController::class, 'updateFeesPaid']);
Route::middleware(['auth:sanctum'])->delete('/delete-payment-record/{fee_id}', [FeePaymentController::class, 'deleteFeePaid']);
Route::middleware(['auth:sanctum'])->get('/indebted-students', [FeePaymentController::class, 'getFeeDebtors']);
Route::middleware(['auth:sanctum'])->post('/payRegistrationFee', [FeePaymentController::class, 'payRegistrationFees']);
Route::middleware(['auth:sanctum'])->get('/getTuitionFees', [FeePaymentController::class, 'getTuitionFees']);
Route::middleware(['auth:sanctum'])->get("/getRegistrationFees", [FeePaymentController::class, 'getRegistrationFees']);
Route::middleware(['auth:sanctum'])->get("/getTransactions", [FeePaymentController::class, 'getTuitionFeeTransactions']);
Route::middleware(['auth:sanctum'])->delete('/reverseTuitionFeeTransaction/{transactionId}', [FeePaymentController::class, 'reverseTuitionFeeTransaction']);
Route::middleware(['auth:sanctum'])->delete('/deleteTransaction/{transactionId}', [FeePaymentController::class, 'deleteTuitionFeeTransaction']);
Route::middleware(['auth:sanctum'])->get("/getTuitionFeeTransactionDetails/{transactionId}", [FeePaymentController::class, 'getTuitionTransactionFeeDetails']);
Route::middleware(['auth:sanctum'])->get('/getRegistrationFeeTransactions', [FeePaymentController::class, 'getRegistrationFeeTransactions']);
Route::middleware(['auth:sanctum'])->delete('/reverseRegistrationFeeTransaction/{transactionId}', [FeePaymentController::class, 'reverseRegistrationFeeTransaction']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteTuitionFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkDeleteTuitionFeeTransactions']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteRegistrationFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkDeleteRegistrationFeeTransactions']);
Route::middleware(['auth:sanctum'])->post('/bulkReverseFeeTuitionFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkReverseTuitionFeeTransaction']);
Route::middleware(['auth:sanctum'])->post('/bulkReverseRegistrationFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkReverseRegistrationFeeTransaction']);
Route::middleware(['auth:sanctum'])->post('/bulkPayRegistrationFee', [FeePaymentController::class, 'bulkPayRegistrationFee']);
