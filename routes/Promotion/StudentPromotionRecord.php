<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Promotion\StudentPromotionRecordController;

Route::post('/promote', [StudentPromotionRecordController::class, 'promoteStudent'])->name('promote.promoteStudent');
Route::post('/repeat', [StudentPromotionRecordController::class, 'repeatStudent'])->name('repeat.promoteStudent');
Route::get('/{recordId}/log', [StudentPromotionRecordController::class, 'getStudentPromotionLog'])->name('get.studentPromotionLog');
Route::get('specialty/{specialty}/next-specialty', [StudentPromotionRecordController::class, 'getNextPromotionSpecialty'])->name('get.nextSpecialty');
Route::get('/{recordId}/details', [StudentPromotionRecordController::class, 'getStudentPromotionDetails'])->name('get.promotionRecordDetails');
Route::get('/', [StudentPromotionRecordController::class, 'getStudentPromotionRecords'])->name('get.promotionRecords');
