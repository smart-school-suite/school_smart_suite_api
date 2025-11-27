<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdditionalFee\AdditionalFeeCategoryController;
// Create a new additional fee category
Route::middleware(['permission:schoolAdmin.additionalFeeCategory.create'])->post('/', [AdditionalFeeCategoryController::class, 'createAddtionalFeeCategory'])
    ->name('additional-fee-categories.store');

// Get all additional fee categories
Route::middleware(['permission:schoolAdmin.additionalFeeCategory.view'])->get('/', [AdditionalFeeCategoryController::class, 'getAdditionalFeeCategory'])
    ->name('additional-fee-categories.index');

// Update a specific additional fee category
Route::middleware(['permission:schoolAdmin.additionalFeeCategory.update'])->put('/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'updateAdditionalFeeCategory'])
    ->name('additional-fee-categories.update');

// Delete a specific additional fee category
Route::middleware(['permission:schoolAdmin.additionalFeeCategory.delete'])->delete('/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'deleteAdditionalFeeCategory'])
    ->name('additional-fee-categories.destroy');
