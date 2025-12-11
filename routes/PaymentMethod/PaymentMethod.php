<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethod\PaymentMethodController;

Route::post('/create', [PaymentMethodController::class, 'createPaymentMethod'])->name('create.paymentMethod');
Route::put('/{paymentMethodId}/update', [PaymentMethodController::class, 'updatePaymentMethod'])->name('update.paymentMethod');
Route::get('/country/{countryId}', [PaymentMethodController::class, 'getPaymentMethodCountryId'])->name('get.country.paymentMethod');
Route::post('/deactivate/{paymentMethodId}', [PaymentMethodController::class, 'deactivatePaymentMethod'])->name('deactivate.paymentMethod');
Route::post('/activate/{paymentMethodId}', [PaymentMethodController::class, 'activatePaymentMethod'])->name('activate.paymentMethod');
Route::get('/{paymentMethodId}', [PaymentMethodController::class, 'getPaymentMethodDetail'])->name('get.paymentMethod.details');
Route::get('/', [PaymentMethodController::class, 'getAllPaymentMethod'])->name('get.paymentMethod');
Route::delete('/{paymentMethodId}/delete', [PaymentMethodController::class, 'deletePaymentMethod'])->name('delete.paymentMethod');
