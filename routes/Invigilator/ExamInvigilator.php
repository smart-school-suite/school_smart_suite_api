<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Invigilator\ExamInvigilatorController;

Route::post("/assign", [ExamInvigilatorController::class, "assignInvigilator"])->name("assign.invigilator");
Route::post("/remove-assigned", [ExamInvigilatorController::class, "removeAssignedInvigator"])->name("remove.invigilator");
Route::get("/exam/{examId}", [ExamInvigilatorController::class, "getInvigilatorsExamId"])->name("get.invigilators.examId");
Route::get("/potential/exam/{examId}", [ExamInvigilatorController::class, "getPotAssignableInvigilators"])->name("get.potentialInvigilators");
