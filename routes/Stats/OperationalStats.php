<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\OperationalStatController;

Route::get("/student/total", [OperationalStatController::class, "getStudentTotal"])->name("student.total");
Route::get("/student/registration-source/{year}", [OperationalStatController::class, "getStudentRegistrationSource"])->name("student.registration.source");
Route::get("/card-stats", [OperationalStatController::class,  "getCardStats"])->name("card.stats");
Route::get("/student-dropout/rate/{year}", [OperationalStatController::class, "getStudentDropoutRate"])->name("student.dropout.rate");
Route::get("/student-dropout/rate/level/{year}", [OperationalStatController::class, "getStudentLevelDropoutRate"])->name("student.dropout.rate.level");
Route::get("/student-retention/rate/level/{year}", [OperationalStatController::class,"getStudentLevelRetentionRate"])->name("student.retention.rate.level");
Route::get("/student-retention/rate", [OperationalStatController::class, "getStudentRetentionRate"])->name("student.retention.rate");
Route::get("/teacher-student/ratio/level", [OperationalStatController::class, "getTeacherStudentRatiolLevel"])->name("teacher.student.ratio.level");
Route::get("/teacher-student/ratio", [OperationalStatController::class, "getTeacherStudentRatio"])->name("teacher.student.ratio");
Route::get("/student-registration/{year}", [OperationalStatController::class, "getStudentRegistration"])->name("student.registration");
Route::get("/student-registration/level/{year}", [OperationalStatController::class, "getStudentLevelRegistration"])->name("student.registration.level");
Route::get("/teacher-retention/rate", [OperationalStatController::class, "getTeacherRetentionRate"])->name("teacher.retention.rate");
