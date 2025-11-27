<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TuitionFee\TuitionFeeInstallmentController;
use App\Http\Middleware\IdentifyTenant;

Route::get('/', [TuitionFeeInstallmentController::class, 'getFeeInstallment'])->name("fee-installment.get");
Route::post('/', [TuitionFeeInstallmentController::class, 'createFeeInstallment'])->name('fee-installment.create');
Route::patch('/{installmentId}', [TuitionFeeInstallmentController::class, 'updateFeeInstallment'])->name('fee-installment.update');
Route::delete('/{installmentId}', [TuitionFeeInstallmentController::class, 'deleteFeeInstallment'])->name('fee-installment.create');
Route::middleware( [IdentifyTenant::class])->get('/status/active', [TuitionFeeInstallmentController::class, 'getActiveFeeInstallment'])->name("fee-installment.get.active");
