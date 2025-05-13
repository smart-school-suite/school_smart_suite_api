<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;

// Create a new fee waiver
Route::post('/fee-waivers', [FeeWaiverController::class, 'createFeeWaiver'])
    ->name('fee-waivers.store');

// Get all fee waivers
Route::get('/fee-waivers', [FeeWaiverController::class, 'getAllFeeWaiver'])
    ->name('fee-waivers.index');

// Get fee waivers for a specific student
Route::get('/students/{studentId}/fee-waivers', [FeeWaiverController::class, 'getByStudent'])
    ->name('students.fee-waivers.index');

// Update a specific fee waiver
Route::put('/fee-waivers/{feeWaiverId}', [FeeWaiverController::class, 'updateFeeWaiver'])
    ->name('fee-waivers.update');

// Delete a specific fee waiver
Route::delete('/fee-waivers/{feeWaiverId}', [FeeWaiverController::class, 'deleteFeeWaiver'])
    ->name('fee-waivers.destroy');
