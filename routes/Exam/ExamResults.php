<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamResultController;


Route::get('/exams', [ExamResultController::class, 'getExams'])
    ->name('result.exams');

Route::get('/exam/{examId}/standing', [ExamResultController::class, 'getExamStandings'])
    ->name('exam.standing');

Route::get('/exam/{examId}/progress', [ExamResultController::class, 'getExamEvaluationProgress'])
    ->name('getExamEvaluationProgress');

Route::post('/publish', [ExamResultController::class, 'publishExamResult'])->name('publish.examResult');

Route::get('/exam/{examId}/publish-details', [ExamResultController::class, 'getExamResultPublishDetails'])
    ->name('get.ExamResultPublishDetails');
