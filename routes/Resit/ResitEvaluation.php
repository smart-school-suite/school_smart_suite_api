<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitEvaluation\ResitEvaluationController;

Route::post('/resit-scores/create', [ResitEvaluationController::class, 'submitResitScores'])
    ->name('resit-scores.store');

Route::put('/resit-scores/update', [ResitEvaluationController::class, 'updateResitScores'])
    ->name('resit-scores.update');
