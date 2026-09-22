<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitEvaluation\ResitEvaluationHelperController;

Route::get("candidate/{candidateId}/exam-helper", [ResitEvaluationHelperController::class, 'resitEvaluationHelperData'])->name("resit.exam.helper");
Route::get("candidate/{candidateId}/update-helper", [ResitEvaluationHelperController::class, 'updateResitEvaluationHelper'])->name("resit.exam.update.helper");
