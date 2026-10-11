<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitExam\ResitScoreController;

Route::get("candidate/{candidateId}/resit-exam", [ResitScoreController::class, "getResitScoresCandidateId"])->name("resit.exam.scores");
Route::delete("candidate/{candidateId}/resit-exam", [ResitScoreController::class, "deleteResitScoresCandidateId"])->name("resit.delete.scores");
