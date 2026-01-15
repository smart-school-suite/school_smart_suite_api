<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\AcademicStatController;

Route::get("/card-stats/{year}", [AcademicStatController::class, "getCardStats"])->name("academic.card.stats");
Route::get("/exam-type/fail-rate/{year}", [AcademicStatController::class, "getExamTypeFailRate"])->name("exam.type.fail.rate");
Route::get("/level/fail-rate/{year}", [AcademicStatController::class, "getLevelFailRate"])->name("level.fail.rate");
Route::get("/school/fail-rate/{year}", [AcademicStatController::class, "getSchoolFailRate"])->name("school.fail.rate");
Route::get("/exam-type/average-gpa/{year}", [AcademicStatController::class, "getExamTypeAverageGpa"])->name("exam.type.average.gpa");
Route::get("/level/average-gpa/{year}", [AcademicStatController::class, "getLevelAverageGpa"])->name("level.average.gpa");
Route::get("/school/average-gpa/{year}", [AcademicStatController::class, "getSchoolAverageGpa"])->name("school.average.gpa");
Route::get("/exam-type/grade-distribution/{year}", [AcademicStatController::class, "getExamTypeGradeDistribution"])->name("exam.type.grade.distribution");
Route::get("/level/grade-distribution/{year}", [AcademicStatController::class, "getLevelGradeDistribution"])->name("level.grade.distribution");
Route::get("/school/grade-distribution/{year}", [AcademicStatController::class, "getSchoolGradeDistribution"])->name("school.grade.distribution");
Route::get("/exam-type/pass-rate/{year}", [AcademicStatController::class, "getExamTypePassRate"])->name("exam.type.fail.rate");
Route::get("/level/pass-rate/{year}", [AcademicStatController::class, "getLevelPassRate"])->name("level.fail.rate");
Route::get("/school/pass-rate/{year}", [AcademicStatController::class, "getSchoolPassRate"])->name("school.pass.rate");
Route::get("/exam-type/resit-total/{year}", [AcademicStatController::class, "getExamTypeResit"])->name("exam.type.resit.total");
Route::get("/level/resit-total/{year}", [AcademicStatController::class, "getResitLevelTotal"])->name("level.resit.total");
Route::get("/resit/success-rate/{year}", [AcademicStatController::class, "getResitSuccessRate"])->name("resit.success.rate");
Route::get("/school/resit-total/{year}", [AcademicStatController::class, "getSchoolResitTotal"])->name("school.resit.total");
