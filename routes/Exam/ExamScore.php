<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamScoreController;


Route::get("candidate/{candidateId}/ca", [ExamScoreController::class, "getCaExamScoreCandidateId"])->name("ca.scores");
Route::get("candidate/{candidateId}/exam", [ExamScoreController::class, "getExamScoreCandidateId"])->name("exam.scores");
Route::delete("candidate/{candidateId}/exam", [ExamScoreController::class, "deleteExamScores"])->name("delete.scores");
Route::delete("candidate/{candidateId}/ca", [ExamScoreController::class, "deleteCaScores"])->name("delete.scores");
