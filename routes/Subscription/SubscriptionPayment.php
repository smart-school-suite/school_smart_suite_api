<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPaymentController;

Route::delete('/delete-transaction', [SubscriptionPaymentController::class, 'deletePayment']);
Route::get('/my-transactions/{school_id}', [SubscriptionPaymentController::class, 'getTransactionsBySchool']);
Route::get('/payment-transactions/{school_id}', [SubscriptionPaymentController::class, 'getAllTransactions']);
