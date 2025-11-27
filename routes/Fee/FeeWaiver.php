<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TuitionFee\TuitionFeeWaiverController;

// Create a new fee waiver
Route::middleware(['permission:schoolAdmin.feeWaiver.create'])->post('/', [TuitionFeeWaiverController::class, 'createFeeWaiver'])
    ->name('fee-waivers.store');

// Get all fee waivers
Route::middleware(['permission:schoolAdmin.feeWaiver.view'])->get('/', [TuitionFeeWaiverController::class, 'getAllFeeWaiver'])
    ->name('fee-waivers.index');

// Get fee waivers for a specific student
Route::middleware(['permission:schoolAdmin.feeWaiver.view.student'])->get('/student/{studentId}/', [TuitionFeeWaiverController::class, 'getByStudent'])
    ->name('students.fee-waivers.index');

// Update a specific fee waiver
Route::middleware(['permission:schoolAdmin.feeWaiver.update'])->put('/{feeWaiverId}', [TuitionFeeWaiverController::class, 'updateFeeWaiver'])
    ->name('fee-waivers.update');

// Delete a specific fee waiver
Route::middleware(['permission:schoolAdmin.feeWaiver.delete'])->delete('/{feeWaiverId}', [TuitionFeeWaiverController::class, 'deleteFeeWaiver'])
    ->name('fee-waivers.destroy');
