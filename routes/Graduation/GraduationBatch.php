<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Graduation\GraduationBatchController;

Route::get('/', [GraduationBatchController::class, 'getGraduationBatches'])->name('get.graduationBatches');
Route::get('{batchId}/details', [GraduationBatchController::class, 'getGraduationBatchDetails'])->name('get.graduationBatch');
Route::put('{batchId}/update', [GraduationBatchController::class, 'updateGraduationBatch'])->name('update.graduationBatches');
