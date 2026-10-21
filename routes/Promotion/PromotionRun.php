<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Promotion\PromotionRunController;


Route::get('/', [PromotionRunController::class, 'getPromotionRuns'])->name('get.promotionRuns');
Route::post('/trigger', [PromotionRunController::class, 'triggerPromotionRun'])->name('trigger.triggerPromotionRun');
Route::get('/{runId}/log', [PromotionRunController::class, 'getPromotionRunLog'])->name('get.getPromotionRunLog');
Route::post('/{runId}/retry', [PromotionRunController::class, 'retryPromotionRun'])->name('retry.retryPromotionRun');
Route::get('/{runId}', [PromotionRunController::class, 'getPromotionRunById'])->name('get.getPromotionRunById');
Route::get('/{runId}/cancel', [PromotionRunController::class, 'cancelPromotionRun'])->name('cancel.cancelPromotionRun');
