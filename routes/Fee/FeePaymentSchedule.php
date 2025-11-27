<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TuitionFee\TuitionFeeScheduleController;
Route::get('/', [TuitionFeeScheduleController::class, 'getFeeSchedule'])->name('fee-schedule.index');
Route::delete('/{feeScheduleId}', [TuitionFeeScheduleController::class, 'deleteFeeSchedule'])->name('fee-schedule.delete');
Route::get('/student/{studentId}', [TuitionFeeScheduleController::class, 'getFeeScheduleStudentId'])->name('student-fee-schedule.get');
Route::get('{feeScheduleId}/slots/', [TuitionFeeScheduleController::class, 'getFeeScheduleSlots'])->name('fee-schedule-slots.details');
Route::post('/slot/create/{feeScheduleId}', [TuitionFeeScheduleController::class, 'createFeeScheduleSlots'])->name('fee-schedule-slots.create');
Route::put('/slot/update/{feeScheduleId}', [TuitionFeeScheduleController::class, 'updateFeeScheduleSlots'])->name('fee-schedule-slots');
Route::post('/auto-generate', [TuitionFeeScheduleController::class, 'autoCreateFeePaymentSchedule'])->name('auto-generate.fee.schedule');
Route::get('/payment-schedule/student', [TuitionFeeScheduleController::class, 'getStudentFeeSchedule'])->name('get.student.fee.payment.shedule');
Route::get('/level/{levelId}/student/fee-schedule', [TuitionFeeScheduleController::class, "getStudentFeeScheduleLevelId"])->name("get.student.fee.shedule.by.levelId");
