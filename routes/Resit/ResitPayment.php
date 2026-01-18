<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Resit\ResitPaymentController;


Route::post('/pay-resit', [ResitPaymentController::class, 'payResit'])
 ->name('resit-payments.pay');

Route::get('/resit-transactions', [ResitPaymentController::class, 'getResitPaymentTransactions'])
 ->name('resit-transactions.index');

Route::delete('/resit-transactions/{transactionId}', [ResitPaymentController::class, 'deleteFeePaymentTransaction'])
 ->name('resit-transactions.destroy');

Route::get('/resit-transactions/{transactionId}', [ResitPaymentController::class, 'getTransactionDetails'])
 ->name('resit-transactions.show');


Route::delete('/resit-transactions/{transactionId}/reverse', [ResitPaymentController::class, 'reverseTransaction'])
 ->name('resit-transactions.reverse');

Route::post('/student-resits/bulk-pay', [ResitPaymentController::class, 'bulkPayStudentResit'])
->name('student-resits.bulk-pay');

Route::post('/resit-transactions/bulk-delete', [ResitPaymentController::class, 'bulkDeleteStudentResitTransactions'])
->name('resit-transactions.bulk-delete');


Route::post('/resit-transactions/bulk-reverse', [ResitPaymentController::class, 'bulkReverseTransaction'])
->name('resit-transactions.bulk-reverse');
