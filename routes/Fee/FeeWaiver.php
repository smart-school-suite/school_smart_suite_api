<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;

// Create a new fee waiver
Route::middleware(['permission:schoolAdmin.feeWaiver.create'])->post('/fee-waivers', [FeeWaiverController::class, 'createFeeWaiver'])
    ->name('fee-waivers.store');

// Get all fee waivers
Route::middleware(['permission:schoolAdmin.feeWaiver.view'])->get('/fee-waivers', [FeeWaiverController::class, 'getAllFeeWaiver'])
    ->name('fee-waivers.index');

// Get fee waivers for a specific student
Route::middleware(['permission:schoolAdmin.feeWaiver.view.student'])->get('/students/{studentId}/fee-waivers', [FeeWaiverController::class, 'getByStudent'])
    ->name('students.fee-waivers.index');

// Update a specific fee waiver
Route::middleware(['permission:schoolAdmin.feeWaiver.update'])->put('/fee-waivers/{feeWaiverId}', [FeeWaiverController::class, 'updateFeeWaiver'])
    ->name('fee-waivers.update');

// Delete a specific fee waiver
Route::middleware(['permission:schoolAdmin.feeWaiver.delete'])->delete('/fee-waivers/{feeWaiverId}', [FeeWaiverController::class, 'deleteFeeWaiver'])
    ->name('fee-waivers.destroy');
