<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Graduation\GraduationRecordController;

Route::get('/', [GraduationRecordController::class, 'getGraduationRecords'])->name('get.graduationRecords');
Route::get('{recordId}/details', [GraduationRecordController::class, 'graduationDetails'])->name('get.graduationDetails');
Route::post('/block', [GraduationRecordController::class, 'blockGraduation'])->name('block.graduationRecord');
Route::post('/unblock', [GraduationRecordController::class, 'unBlockGraduation'])->name('unblock.graduationRecord');
Route::post('/approve-eligibility', [GraduationRecordController::class, 'approveEligibility'])->name('graduationRecords.approveEligibility');
Route::post('/mark-ineligibility', [GraduationRecordController::class, 'markInEligible'])->name('graduationRecords.markInEligibility');
Route::post('/request-review', [GraduationRecordController::class, 'requestReview'])->name('graduationRecords.requestReview');
Route::post('/approve-continuation', [GraduationRecordController::class, 'approveContinuation'])->name('graduationRecords.approveContinuation');
Route::post('/approve-alumnus', [GraduationRecordController::class, 'confirmAlumnus'])->name('graduationRecords.confirmAlumnus');
