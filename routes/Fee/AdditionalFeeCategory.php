<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdditionalFee\AdditionalFeeCategoryController;


Route::post('/', [AdditionalFeeCategoryController::class, 'createAddtionalFeeCategory'])
    ->name('additional-fee-categories.store');

Route::get('/', [AdditionalFeeCategoryController::class, 'getAdditionalFeeCategory'])
    ->name('additional-fee-categories.index');

Route::put('/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'updateAdditionalFeeCategory'])
    ->name('additional-fee-categories.update');

Route::delete('/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'deleteAdditionalFeeCategory'])
    ->name('additional-fee-categories.destroy');
