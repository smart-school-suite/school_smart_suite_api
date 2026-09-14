<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationHelperController;

Route::get("candidate/{candidateId}/exam-helper", [ExamEvaluationHelperController::class, 'getExamEvaluationHelperData'])->name("exam.helper");
Route::get("candidate/{candidateId}/ca-helper", [ExamEvaluationHelperController::class, 'getCaExamEvaluationHelperData'])->name("exam.helper");
