<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationController;



Route::post('/ca-scores/create', [ExamEvaluationController::class, 'createCaMark'])
    ->name('ca-scores.store');

Route::post('/exam-scores/create', [ExamEvaluationController::class, 'createExamMark'])
    ->name('exam-scores.store');

Route::put('/ca-scores/update', [ExamEvaluationController::class, 'updateCaMark'])
    ->name('ca-scores.update');

Route::put('/exam-scores/update', [ExamEvaluationController::class, 'updateExamMark'])
    ->name('exam-scores.update');
