<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Promotion\PromotionRunController;


Route::get('/', [PromotionRunController::class, 'getPromotionRuns'])->name('get.promotionRuns');
