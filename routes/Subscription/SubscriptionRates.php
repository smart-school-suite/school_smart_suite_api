<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatesCardController;
// Get all rates
Route::get('/rates', [RatesCardController::class, 'getAllRates'])
    ->name('rates.index');

Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new rate
    Route::middleware(['permission:appAdmin.rateCard.create'])->post('/rates', [RatesCardController::class, 'createRates'])
        ->name('rates.store');

    // Update a specific rate
    Route::middleware(['permission:appAdmin.rateCard.update'])->put('/rates', [RatesCardController::class, 'updateRates'])
        ->name('rates.update');

    // Delete a specific rate
    Route::middleware(['permission:appAdmin.rateCard.delete'])->delete('/rates/{rateId}', [RatesCardController::class, 'deleteRates'])
        ->name('rates.destroy');
});
