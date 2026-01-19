<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivationCode\ActivationCodeController;

Route::post('/purchase', [ActivationCodeController::class, 'purchaseActivationCode'])->name("purchase.activationCode");
Route::get('/school', [ActivationCodeController::class, 'getSchoolBranchActivationCodes'])->name("get.schoolBranchActivationCode");
Route::post('/student/activate', [ActivationCodeController::class, 'activateStudentAccount'])->name("activate.studentAccount.activationCode");
Route::post('/teacher/activate', [ActivationCodeController::class, 'activateTeacherAccount'])->name("activate.teacherAccount.activationCode");
Route::get("/usage", [ActivationCodeController::class, "getActivationCodeUsage"])->name("activationCodeUsage.index");
Route::get('/status/student', [ActivationCodeController::class,  "getStudentActivationCodeStatus"])->name("activationCodeStatus.Student");
Route::get('/status/teacher', [ActivationCodeController::class,  "getTeacherActivationCodeStatus"])->name("activationCodeStatus.Teacher");
Route::get('/student/subscription/{studentId}', [ActivationCodeController::class,  "getStudentSubscriptionDetail"])->name("student.subscription.detail");
Route::get('/teacher/subscription/{teacherId}', [ActivationCodeController::class,  "getTeacherSubscriptionDetail"])->name("teacher.subscription.detail");
