<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatesCardController;

Route::post('/create-rate', [RatesCardController::class, 'createRates']);
Route::put('/update-rate', [RatesCardController::class, 'updateRates']);
Route::delete('/delete-rate/{rate_id}', [RatesCardController::class, 'deleteRates']);
Route::get('/rates', [RatesCardController::class, 'getAllRates']);
