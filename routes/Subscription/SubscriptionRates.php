<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatesCardController;
// Get all rates
Route::get('/rates', [RatesCardController::class, 'getAllRates'])
    ->name('rates.index');

Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new rate
    Route::post('/rates', [RatesCardController::class, 'createRates'])
        ->name('rates.store');

    // Update a specific rate
    Route::put('/rates', [RatesCardController::class, 'updateRates'])
        ->name('rates.update');

    // Delete a specific rate
    Route::delete('/rates/{rateId}', [RatesCardController::class, 'deleteRates'])
        ->name('rates.destroy');
});
