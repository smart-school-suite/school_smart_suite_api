<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdditionalFeeCategoryController;

Route::post('/createCategory', [AdditionalFeeCategoryController::class, 'createAddtionalFeeCategory']);
Route::get('/getCategory', [AdditionalFeeCategoryController::class, 'getAdditionalFeeCategory']);
Route::delete('/deletCategory/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'deleteAdditionalFeeCategory']);
Route::put('/updateCategory/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'updateAdditionalFeeCategory']);
