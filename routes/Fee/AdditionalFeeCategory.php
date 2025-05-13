<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdditionalFeeCategoryController;

// Create a new additional fee category
Route::post('/additional-fee-categories', [AdditionalFeeCategoryController::class, 'createAddtionalFeeCategory'])
    ->name('additional-fee-categories.store');

// Get all additional fee categories
Route::get('/additional-fee-categories', [AdditionalFeeCategoryController::class, 'getAdditionalFeeCategory'])
    ->name('additional-fee-categories.index');

// Update a specific additional fee category
Route::put('/additional-fee-categories/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'updateAdditionalFeeCategory'])
    ->name('additional-fee-categories.update');

// Delete a specific additional fee category
Route::delete('/additional-fee-categories/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'deleteAdditionalFeeCategory'])
    ->name('additional-fee-categories.destroy');
