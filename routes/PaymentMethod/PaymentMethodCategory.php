<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethod\PaymentMethodCategoryController;

Route::post('/create', [PaymentMethodCategoryController::class, 'createCategory'])->name('create.category');
Route::put('/{categoryId}/update', [PaymentMethodCategoryController::class, 'updateCategory'])->name('update.category');
Route::get('/', [PaymentMethodCategoryController::class, 'getCategory'])->name('get.category');
Route::delete('/{categoryId}/delete', [PaymentMethodCategoryController::class, 'deleteCategory'])->name('delete.category');
Route::post('/{categoryId}/activate', [PaymentMethodCategoryController::class, 'activateCategory'])->name('activate.category');
Route::post('/{categoryId}/deactivate', [PaymentMethodCategoryController::class, 'deactivateCategory'])->name('deactivate.category');
Route::get('/{categoryId}', [PaymentMethodCategoryController::class, 'getCategoryDetails'])->name('get.deatail');
