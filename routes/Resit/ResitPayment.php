<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Resit\ResitPaymentController;


// Record payment for a resit middleware(['permission:schoolAdmin.studentResits.pay'])
Route::post('/pay-resit', [ResitPaymentController::class, 'payResit'])
 ->name('resit-payments.pay');

 // Get all resit payment transactions (potentially admin-only, consider prefixing with /admin)
Route::middleware(['permission:schoolAdmin.studentResits.transactions.view'])->get('/resit-transactions', [ResitPaymentController::class, 'getResitPaymentTransactions'])
 ->name('resit-transactions.index');

// Delete a specific resit payment transaction
Route::middleware(['permission:schoolAdmin.studentResits.transactions.delete'])->delete('/resit-transactions/{transactionId}', [ResitPaymentController::class, 'deleteFeePaymentTransaction'])
 ->name('resit-transactions.destroy');

 // Get details of a specific resit payment transaction
Route::middleware(['permission:schoolAdmin.studentResits.transaction.show'])->get('/resit-transactions/{transactionId}', [ResitPaymentController::class, 'getTransactionDetails'])
 ->name('resit-transactions.show');
// Reverse a specific resit payment transaction
Route::middleware(['permission:schoolAdmin.studentResits.transaction.reverse'])->delete('/resit-transactions/{transactionId}/reverse', [ResitPaymentController::class, 'reverseTransaction'])
 ->name('resit-transactions.reverse');

 // Bulk payment for student resits middleware(['permission:schoolAdmin.studentResits.pay'])->
Route::post('/student-resits/bulk-pay', [ResitPaymentController::class, 'bulkPayStudentResit'])
->name('student-resits.bulk-pay');

// Bulk deletion of resit transactions
Route::middleware(['permission:schoolAdmin.studentResits.transactions.delete'])->post('/resit-transactions/bulk-delete', [ResitPaymentController::class, 'bulkDeleteStudentResitTransactions'])
->name('resit-transactions.bulk-delete');

// Bulk reversal of resit transactions
Route::middleware(['permission:schoolAdmin.studentResits.transaction.reverse'])->post('/resit-transactions/bulk-reverse', [ResitPaymentController::class, 'bulkReverseTransaction'])
->name('resit-transactions.bulk-reverse');
