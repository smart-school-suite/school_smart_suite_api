<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPaymentController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Get all subscription transactions for a specific school
    Route::get('/schools/{schoolId}/subscription-transactions', [SubscriptionPaymentController::class, 'getAllTransactions'])
        ->name('schools.subscription-transactions.index');

    // Get subscription transactions for the authenticated school
    Route::get('/my-subscription-transactions', [SubscriptionPaymentController::class, 'getTransactionsBySchool'])
        ->name('my-subscription-transactions.index');

    // Delete a specific subscription payment transaction
    Route::delete('/subscription-transactions', [SubscriptionPaymentController::class, 'deletePayment'])
        ->name('subscription-transactions.destroy');
});
