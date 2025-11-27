<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\RateCardController;
// Get all rates
Route::get('/rates', [RateCardController::class, 'getAllRates'])
    ->name('rates.index');

Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new rate
    Route::middleware(['permission:appAdmin.rateCard.create'])->post('/rates', [RateCardController::class, 'createRates'])
        ->name('rates.store');

    // Update a specific rate
    Route::middleware(['permission:appAdmin.rateCard.update'])->put('/rates', [RateCardController::class, 'updateRates'])
        ->name('rates.update');

    // Delete a specific rate
    Route::middleware(['permission:appAdmin.rateCard.delete'])->delete('/rates/{rateId}', [RateCardController::class, 'deleteRates'])
        ->name('rates.destroy');
});
