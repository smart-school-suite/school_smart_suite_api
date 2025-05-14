<?php

use App\Http\Controllers\FeePaymentScheduleController;
use Illuminate\Support\Facades\Route;

// Create a new fee payment schedule
Route::middleware(['permission:schoolAdmin.feeSchedule.create'])->post('/fee-payment-schedules', [FeePaymentScheduleController::class, 'createFeePaymentSchedule'])
    ->name('fee-payment-schedules.store');

// Get all fee payment schedules
Route::middleware(['permission:schoolAdmin.feeSchedule.view'])->get('/fee-payment-schedules', [FeePaymentScheduleController::class, 'getAllFeePaymentSchedule'])
    ->name('fee-payment-schedules.index');

// Get fee payment schedules for a specific specialty
Route::middleware(['permission:schoolAdmin.feeSchedule.view.specialty'])->get('/specialties/{specialtyId}/fee-payment-schedules', [FeePaymentScheduleController::class, 'getFeePaymentScheduleBySpecialty'])
    ->name('specialties.fee-payment-schedules.index');

// Update a specific fee payment schedule
Route::middleware(['permission:schoolAdmin.feeSchedule.update'])->put('/fee-payment-schedules/{scheduleId}', [FeePaymentScheduleController::class, 'updateFeePaymentSchedule'])
    ->name('fee-payment-schedules.update');

// Delete a specific fee payment schedule
Route::middleware(['permission:schoolAdmin.feeSchedule.delete'])->delete('/fee-payment-schedules/{scheduleId}', [FeePaymentScheduleController::class, 'deleteFeePaymentSchedule'])
    ->name('fee-payment-schedules.destroy');
