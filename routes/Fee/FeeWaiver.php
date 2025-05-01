<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;

Route::post('/createFeeWaiver', [FeeWaiverController::class, 'createFeeWaiver']);
Route::put('/updateFeeWaiver/{feeWaiverId}', [FeeWaiverController::class, 'updateFeeWaiver']);
Route::get('/getByStudent/{studentId}', [FeeWaiverController::class, 'getByStudent']);
Route::delete('/deleteFeeWaiver/{feeWaiverId}', [FeeWaiverController::class, 'deleteFeeWaiver']);
Route::get('/getAllFeeWaivers', [FeeWaiverController::class, 'getAllFeeWaiver']);
