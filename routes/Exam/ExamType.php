<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamType\ExamTypeController;
use App\Http\Middleware\IdentifyTenant;

Route::post('/', [ExamTypeController::class, 'createExamType'])
    ->name('exam-types.store');

Route::delete('/{examTypeId}', [ExamTypeController::class, 'deleteExamType'])
    ->name('exam-types.destroy');

Route::put('/{examTypeId}', [ExamTypeController::class, 'updateExamType'])
    ->name('exam-types.update');

Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/', [ExamTypeController::class, 'getExamType'])
    ->name('exam-types.index');
