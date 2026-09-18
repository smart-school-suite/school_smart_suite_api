<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationHelperController;

Route::get("candidate/{candidateId}/exam-helper", [ExamEvaluationHelperController::class, 'getExamEvaluationHelperData'])->name("exam.helper");
Route::get("candidate/{candidateId}/ca-helper", [ExamEvaluationHelperController::class, 'getCaExamEvaluationHelperData'])->name("exam.helper");
Route::get("candidate/{candidateId}/ca-update/helper", [ExamEvaluationHelperController::class, "getCaExamUpdateHelperData"])->name("candidate.update.helper");
Route::get("candidate/{candidateId}/exam-update/helper", [ExamEvaluationHelperController::class, "getExamUpdateHelperData"])->name("candidate.exam.update.helper");
