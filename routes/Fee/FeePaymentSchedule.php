<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeScheduleController;

Route::get('/', [FeeScheduleController::class, 'getFeeSchedule'])->name('fee-schedule.index');
Route::delete('/{feeScheduleId}', [FeeScheduleController::class, 'deleteFeeSchedule'])->name('fee-schedule.delete');
Route::get('/student/{studentId}', [FeeScheduleController::class, 'getStudentFeeSchedule'])->name('student-fee-schedule.get');
Route::get('{feeScheduleId}/slots/', [FeeScheduleController::class, 'getFeeScheduleSlots'])->name('fee-schedule-slots.details');
Route::post('/slot/create/{feeScheduleId}', [FeeScheduleController::class, 'createFeeScheduleSlots'])->name('fee-schedule-slots.create');
Route::put('/slot/update/{feeScheduleId}', [FeeScheduleController::class, 'updateFeeScheduleSlots'])->name('fee-schedule-slots');
Route::post('/auto-generate', [FeeScheduleController::class, 'autoCreateFeePaymentSchedule'])->name('auto-generate.fee.schedule');
