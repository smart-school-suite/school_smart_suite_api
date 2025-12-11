<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\SubscriptionPaymentController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    Route::get('/transaction', [SubscriptionPaymentController::class, 'getTransactions'])->name('get.subscription.transactions');
    Route::delete('/transaction/{transactionId}', [SubscriptionPaymentController::class, 'deleteTransaction'])->name('delete.subscription.transactions');
    Route::get('/transaction/{transactionId}', [SubscriptionPaymentController::class, 'getTransactionDetails'])->name('get.transaction.details');
});
