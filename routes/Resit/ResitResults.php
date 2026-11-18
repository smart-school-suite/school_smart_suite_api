<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitResult\ResitResultController;

Route::get('/resit-exams', [ResitResultController::class, 'getResitExams'])
    ->name('resit-results.exams');

Route::post('/publish', [ResitResultController::class, 'publishResitResult'])
    ->name('resit-results.publish');

Route::get('/resit-exam/{resitExamId}/publish-details', [ResitResultController::class, 'getPublishDetails'])
    ->name('resit-results.publish-details');

Route::get('/resit-exam/{resitExamId}/progress', [ResitResultController::class, 'getEvaluationProgress'])
    ->name('resit-results.progress');
