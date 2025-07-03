<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallmentController;
use App\Http\Middleware\IdentifyTenant;

Route::get('/', [InstallmentController::class, 'getFeeInstallment'])->name("fee-installment.get");
Route::post('/', [InstallmentController::class, 'createFeeInstallment'])->name('fee-installment.create');
Route::patch('/{installmentId}', [InstallmentController::class, 'updateFeeInstallment'])->name('fee-installment.update');
Route::delete('/{installmentId}', [InstallmentController::class, 'deleteFeeInstallment'])->name('fee-installment.create');
Route::middleware( [IdentifyTenant::class])->get('/status/active', [InstallmentController::class, 'getActiveFeeInstallment'])->name("fee-installment.get.active");
