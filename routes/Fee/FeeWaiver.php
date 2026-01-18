<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TuitionFee\TuitionFeeWaiverController;


Route::post('/', [TuitionFeeWaiverController::class, 'createFeeWaiver'])
    ->name('fee-waivers.store');

Route::get('/', [TuitionFeeWaiverController::class, 'getAllFeeWaiver'])
    ->name('fee-waivers.index');

Route::get('/student/{studentId}/', [TuitionFeeWaiverController::class, 'getByStudent'])
    ->name('students.fee-waivers.index');

Route::put('/{feeWaiverId}', [TuitionFeeWaiverController::class, 'updateFeeWaiver'])
    ->name('fee-waivers.update');

Route::delete('/{feeWaiverId}', [TuitionFeeWaiverController::class, 'deleteFeeWaiver'])
    ->name('fee-waivers.destroy');
