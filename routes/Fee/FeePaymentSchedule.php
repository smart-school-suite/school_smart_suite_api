<?php
use App\Http\Controllers\FeePaymentScheduleController;
use Illuminate\Support\Facades\Route;

Route::post('/createSchedule', [FeePaymentScheduleController::class, 'createFeePaymentSchedule']);
Route::put('/updateSchedule/{scheduleId}', [FeePaymentScheduleController::class, 'updateFeePaymentSchedule']);
Route::get('/getAllSchedule', [FeePaymentScheduleController::class, 'getAllFeePaymentSchedule']);
Route::get('/getBySpecialty/{specialtyId}', [FeePaymentScheduleController::class, 'getFeePaymentScheduleBySpecialty']);
Route::delete('/deleteSpecialty/{scheduleId}', [FeePaymentScheduleController::class, 'deleteFeePaymentSchedule']);
